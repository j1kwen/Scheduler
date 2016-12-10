<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Log;

class Logs extends Controller {
	
	public function index() {
		
		$this->assign([
				'title' => '系统日志',
		]);
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			$this->assign([
					'logs' => Log::getLog(),
			]);
			return $this->fetch();
		} else {
			$this->error();
		}
	}
}