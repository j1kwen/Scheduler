<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Resource extends Controller {
	
	public function about() {
		if(Request::instance()->isAjax()) {
			
			return $this->fetch('Public/about');
		} else {
			$this->error();
		}
	}
	
	public function debug() {
		return $this->fetch('Public/debug',[
				'title' => 'Debug it',
		]);
	}
}