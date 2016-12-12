<?php

namespace app\index\controller;

use think\Request;

class Course extends BaseController {
	
	public function index() {
		$this->assign([
				'title' => '课程管理',
		]);
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			
		} else {
			$this->error();
		}
	}
}