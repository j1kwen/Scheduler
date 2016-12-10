<?php
namespace app\index\controller;

use think\Controller;
use think\View;
use think\Request;
use think\Session;
use think\Db;

class Login extends Controller
{
    public function index(Request $request)
    {
        if (!Session::has('user', 'scheduler')) {
            return $this->fetch();
        } else {
            $this->redirect('index');
        }
    }
    
    public function verify(Request $request) {
        if ($request->param('user') && $request->param('password')) {
            $user = Db::name('admin')->where(['user' => $request->param('user')])->find();
            if ($user == '') {
                return $this->getAjaxResp("用户名或密码错误!", false);
            } else {
                if ($user['passwd'] == $request->param('password')) {
                    $user['logintime'] = time();
                    $user['loginip'] = $request->ip();
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
    }

    public function out() {
        Session::clear('scheduler');
        $this->redirect('index/login/index');
    }
}

