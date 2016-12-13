<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Loader;

class Resource extends BaseController {
	
	public function about() {
		if(Request::instance()->isAjax()) {
			
			return $this->fetch('public/about');
		} else {
			$this->error();
		}
	}
	
	public function debug() {
		Loader::import('PHPExcel.PHPExcel');
		$excel = new \PHPExcel();
		$sheet = $excel->getActiveSheet();
		$sheet->setTitle('Sheet1');
		$sheet->setCellValue('A1',666);
		exportExcel($excel, 'debug');
		
// 		return $this->fetch('public/debug',[
// 				'title' => 'Debug it',
// 		]);
	}
}