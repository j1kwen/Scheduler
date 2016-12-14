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
	
	public function format() {
		$request = Request::instance();
		if(Request::instance()->isAjax()) {
			$_part = $request->param('part');
			return $this->fetch('public/about');
		} else {
			$this->error();
		}
	}
	
	public function upload() {
		$request = Request::instance();
		$file = $request->file('file');
		$this->assign([
				'list' => getExcelArray($file),
		]);
		return $this->fetch('course/item');
	}
	
}