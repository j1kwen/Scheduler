<?php
use think\Config;
use think\Loader;
use think\File;

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
 * 创建PHPExcel对象
 * @param string $filename 将指定文件实例化成对象，为空则创建新对象
 * @return PHPExcel
 */
function createPHPExcelObj($filename = null) {
	loadPHPExcelLibrary();
	if(isset($filename)) {		
		return PHPExcel_IOFactory::load($filename);
	} else {		
		return new PHPExcel();
	}
}

/**
 * 导出excel文件到浏览器（下载）
 * @param PHPExcel $excel PHPExcel对象
 * @param string $filename 输出的文件名，不加扩展名，默认为当前时间戳秒数
 * @param string $type 文件类型Excel5|Excel2007，默认从配置文件读取
 */
function exportExcel(PHPExcel $excel, $filename = null, $type = null) {
	$type = ifdefault($type, ifdefault(Config::get('excel.type'), 'Excel2007'));
	$filename = ifdefault($filename, time());
	$filename = $filename.($type == 'Excel5' ? '.xls' : '.xlsx');
	if($type == 'Excel5') {
		// Excel5 xls
		header('Content-Type: application/vnd.ms-excel');
	} else {
		// Excel2007 xlsx
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	}
	header('Content-Disposition: attachment;filename="'.$filename.'"');
	header('Cache-Control: max-age=0');
	$writer = PHPExcel_IOFactory::createWriter($excel, $type);
	$writer->save("php://output");
}

/**
 * 导出pdf文件到浏览器（下载）
 * @param PHPExcel $excel PHPExcel对象
 * @param string $filename 输出的文件名，不加扩展名，默认为当前时间戳秒数
 */
function exportPDF(PHPExcel $excel, $filename = null) {
	$filename = ifdefault($filename, time()).'.pdf';
	$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
	$rendererLibraryPath = dirname(__FILE__).'/../extend/PDF/';
	if (!PHPExcel_Settings::setPdfRenderer(
		$rendererName,
		$rendererLibraryPath
		)) {
		die(
			'NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
			'<br />' .
			'at the top of this script as appropriate for your directory structure'
		);
	}
 	header('Content-Type: application/pdf');
 	header('Content-Disposition: attachment;filename="'.$filename.'"');
 	header('Cache-Control: max-age=0');
	
 	$writer = PHPExcel_IOFactory::createWriter($excel, 'PDF');
	$writer->save("php://output");
}

/**
 * 将Excel文件的第一张工作表转换成数组对象
 * @param File $file 请求的File对象
 * @param bool $delete 是否在完成后删除源文件
 * @param bool $trim 是否自动删除末尾空白行
 * @return array
 */
function getExcelArray(File $file = null, $delete = true, $trim = true) {
	if(!isset($file)) {
		return null;
	}
	$info = $file->validate([
			'ext' => 'xls,xlsx',
	])->move(ROOT_PATH . 'public' . DS . 'uploads', timestamp());
	if($info) {
		$filename = $info->getRealPath();
		$excel = createPHPExcelObj($filename);
		unset($info);
		if($delete) {			
			unlink($filename);
		}
		$arr = $excel->getSheet(0)->toArray();
		if(!$trim) {
			return $arr;
		}
		for($i = count($arr) - 1 ; $i >= 0 ; $i--) {
			$isNull = true;
			foreach($arr[$i] as $cell) {
				if(!empty($cell)) {
					$isNull = false;
					break;
				}
			}
			if(!$isNull) {
				break;
			}
			unset($arr[$i]);
		}
		return $arr;
	} else {
		return null;
	}
}

/**
 * 生成json响应字符串（Ajax）
 * @param string $msg 传输的消息
 * @param string $ok 请求是否完成
 * @return \think\response\Json json字符串
 */
function getAjaxResp($msg="error", $ok=false, $code=0) {
	return json([
			"success" => $ok,
			"msg" => $msg,
			"code" => $code,
	]);
}

/**
 * 加载PHPExcel类库
 */
function loadPHPExcelLibrary() {
	Loader::import(ifdefault(Config::get('excel.library'), 'PHPExcel.PHPExcel'));
}

/**
 * 检查对象并设置默认值
 * @param mixed $object 要检查的对象
 * @param mixed $def 默认值
 * @return mixed 对象非空则返回对象，否则返回默认值
 */
function ifdefault($object, $def = null) {
	return isset($object) ? $object : $def;
}
/**
 * 获取Unix时间戳秒数
 * @return int
 */
function timestamp() {
	list($msec, $sec) = explode(' ', microtime());
	$msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	return $msectime;
}