<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Auth;

class Login extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::login()) {
        	if(Session::has('redir','scheduler') && Session::get('redir','scheduler')) {
        		$_alert = '请先登录！';
        		Session::set('redir', false, 'scheduler');
        	}
        	$this->assign([
        			'title' => '用户登录',
        			'alert' => isset($_alert) ? $_alert : null,
        	]);
            return $this->fetch();
        } else {
            $this->redirect('index/index/index');
        }
    }
    
    public function verify(Request $request) {
    	if($request->isAjax()) {
    		$_user = $request->param('user');
    		$_pwd = $request->param('password');
    		$_code = $request->param('captcha');
	        if (!empty($_user) && !empty($_pwd) && !empty($_code)) {
	        	if(!captcha_check($_code)) {
	        		// code error
	        		return $this->getAjaxResp("验证码错误！");
	        	}
	        	$auth = new Auth();
	        	if($auth->authUser($_user, $_pwd, $request->ip())) {
	        		Auth::login($_user, $auth->getName($_user));
                    return json([
                        'success' => true,
                        'url' => url('index/index/index'),
                        'msg' => 'success',
                    ]);
	        	} else {
	        		return $this->getAjaxResp("用户名或密码错误!");
	        	}
	        } else {
	            return $this->getAjaxResp("请输入完整信息!", false);
	        }
    	} else {
    		$this->error();
    	}
    }
    
    public function modify(Request $request) {
    	if($request->isAjax()) {
    		$user = $request->param('user');
    		$old = $request->param('old');
    		$pwd = $request->param('pwd');
    		$pwd2 = $request->param('pwd2');
    		if(Auth::login()) {
    			// has login
    			$cur_user = Session::get('user','scheduler');
    			if($cur_user == $user && !empty($pwd) && !empty($pwd2) && $pwd == $pwd2) {
    				// user ok
    				$auth = new Auth();
    				if($auth->modifyPwd($user, $old, $pwd)) {
    					Auth::logout();
    					return json([
    							'success' => true,
    							'redir' => true,
    							'msg' => '密码修改成功，请重新登录',
    							'url' => url('index/login/index'),
    					]);
    				} else {
    					return $this->getAjaxResp('原密码错误，请重新输入！');
    				}
    			} else {
    				// error user
    				return $this->getAjaxResp('参数非法，请重试！');
    			}
    		} else {
    			// require login
    			return json([
    					'success' => false,
    					'msg' => '登录信息已过期，请重新登录！',
    					'redir' => true,
    					'url' => url('index/login/index'),
    			]);
    		}
    	} else {
    		$this->error();
    	}
    }

    public function out() {
    	Auth::logout();
        $this->redirect('index/login/index');
    }
}

