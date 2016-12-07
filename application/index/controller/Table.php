<?php
namespace app\index\controller;

use think\Controller;
use think\View;

class Table extends Controller {
	
	public function index() {

		$this->assign('data',array(
			array(),
			array(),
		));
		$term = model('term');
		$item = $term->where('is_cur',1)->find();
		if(isset($item)) {			
			$differ = floor(date_diff(date_create($item->start), date_create())->format('%a') / 7) + 1;
		} else {			
			$alert_string = (new View())->assign([
					'type' => 'danger',
					'close' => false,
					'title' => '错误！',
					'body' => '暂未设置当前学期，无法查看课表！',
					'href' => url('index/Term/index'),
					'href_text' => '立即设置',
			])->fetch('Public/alert');
		}
		$this->assign([
				'title' => '课表总览',
				'cols' => 4,
				'rows' => 6,
				'week' => isset($differ)?$differ:null,
				'alert' => isset($alert_string)?$alert_string:null,
		]);
		return $this->fetch();
	}
}