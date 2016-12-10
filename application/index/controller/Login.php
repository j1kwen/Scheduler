<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class Login extends Controller
{
    public function index(Request $request)
    {
        if (!Session::has('user', 'scheduler')) {
        	$this->assign([
        			'title' => '用户登录',
        	]);
            return $this->fetch();
        } else {
            $this->redirect('index/index/index');
        }
    }
    
    public function verify(Request $request) {
    	if($request->isAjax()) {
	        if ($request->param('user') && $request->param('password')) {
	            $user = Db::name('admin')->where(['user' => $request->param('user')])->find();
	            if ($user == '') {
	                return $this->getAjaxResp("用户名或密码错误!", false);
	            } else {
	                if ($user['passwd'] == $request->param('password')) {
	                    $user['logintime'] = time();
	                    $user['loginip'] = $request->ip(0);
	                    Db::name('admin')->where(['user' => $request->param('user')])->update($user);
	                    session('user', $request->param('user'), 'scheduler');
	                    return json([
	                        'success' => true,
	                        'url' => url('index/index/index'),
	                        'msg' => 'success',
	                    ]);
	                } else {
	                    return $this->getAjaxResp("用户名或密码错误!", false);
	                }
	            }
	        } else {
	            return $this->getAjaxResp("请输入完整信息!", false);
	        }
    	} else {
    		$this->error();
    	}
    }

    public function out() {
        Session::clear('scheduler');
        $this->redirect('index/login/index');
    }
}

