<?php

namespace app\index\controller;

use think\Controller;

class Login extends Controller {
	
	public function index() {
		$this->assign([
				'title' => '用户登录',
		]);
		return $this->fetch();
	}
}