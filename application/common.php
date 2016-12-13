<?php
use think\Config;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 导出excel文件到浏览器（下载）
 * @param PHPExcel $phpExcel PHPExcel对象
 * @param string $filename 输出的文件名，不加扩展名，默认为当前时间戳秒数
 * @param string $type 文件类型Excel5|Excel2007，默认从配置文件读取
 */
function exportExcel(PHPExcel $phpExcel, $filename='', $type='') {
	if($type == '') {
		$type = Config::get('excel.type');
	}
	if($filename == '') {
		$filename = time();
	}
	$filename = $filename.($type == 'Excel5' ? '.xls' : '.xlsx');
	$writer = PHPExcel_IOFactory::createWriter($phpExcel, $type);
	if($type == 'Excel5') {		
		header('Content-Type: application/vnd.ms-excel');
	} else {		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	}
	header('Content-Disposition: attachment;filename="'.$filename.'"');
	header('Cache-Control: max-age=0');
	$writer->save("php://output");
}

/**
 * 生成json响应字符串（Ajax）
 * @param string $msg 传输的消息
 * @param string $ok 请求是否完成
 * @return \think\response\Json json字符串
 */
function getAjaxResp($msg="error", $ok=false) {
	return json([
			"success" => $ok,
			"msg" => $msg,
	]);
}