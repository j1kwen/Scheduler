<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Resource extends BaseController {
	
	public function about() {
		if(Request::instance()->isAjax()) {
			
			return $this->fetch('public/about');
		} else {
			$this->error();
		}
	}
	
	public function debug() {
		return $this->fetch('public/debug',[
				'title' => 'Debug it',
		]);
	}
}