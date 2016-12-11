<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Auth;

class BaseController extends Controller {
    
	protected $_auth = false;
	
    public function _initialize() {
    	$this->_auth = Auth::login();
    	if(!$this->_auth) {    		
    		$request = Request::instance();
	    	if($request->isAjax()) {
	    		$this->error('登录信息貌似已经过期，请刷新页面后重新登录！');
	    	} else {
	    		Session::set('redir', true, 'scheduler');
	    		$this->redirect('index/login/index');
	    	}
    	}
    }
}
