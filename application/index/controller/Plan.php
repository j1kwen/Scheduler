<?php

namespace app\index\controller;

use app\index\model\Term;
use think\View;
use think\Request;

class Plan extends BaseController {
	
	public function index() {
		$term = Term::getCurTerm();
		if(isset($term)) {		
			$course = model('course')->getItem($term['code']);
		}
		if(!isset($term)) {
			// no term
			$alert_string = (new View())->assign([
					'type' => 'danger',
					'close' => false,
					'title' => '错误！',
					'body' => '暂未设置当前学期，无法查看和导入上课安排！',
					'href' => url('index/term/index'),
					'href_text' => '立即设置',
			])->fetch('public/alert');
			
		} else if(empty($course)) {
			// set term but no course
			$alert_string = (new View())->assign([
					'type' => 'danger',
					'close' => false,
					'title' => '错误！',
					'body' => '暂未导入本学期的课程数据，无法查看和导入上课安排！',
					'href' => url('index/course/index'),
					'href_text' => '立即导入',
			])->fetch('public/alert');
		} else {
			// ok
			$alert_string = (new View())->assign([
					'type' => 'success',
					'close' => false,
					'title' => '所有条件已就绪！',
					'body' => '现在是<strong>'.$term['year'].'-'.($term['year']+1).'</strong>学年 第<strong>'.$term['term'].'</strong>学期，已导入课程',
					'href' => url('index/course/index'),
					'href_text' => '查看课程',
			])->fetch('public/alert');
		}
		$this->assign([
				'term' => $term,
				'course' => isset($course) ? $course : null,
				'title' => '上课时间管理',
				'alert' => $alert_string,
		]);
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			$term = Term::getCurTerm();
			$course = null;
			if(isset($term)) {
				$course = model('course')->getItem($term['code']);
			}
			$list = null;
			if(isset($term) && !empty($course)) {
				// get list item
				$plan = model('plan');
				$list = $plan->getItem($term['code']);
			}
			$this->assign([
					'term' => $term,
					'course' => isset($course) ? $course : null,
					'list' => $list,
			]);
			return $this->fetch();
		} else {
			$this->error();
		}
	}
	
	public function upload() {
		$request = Request::instance();
		if($request->isAjax()) {
			$file = $request->file('file');
			$term = Term::getCurTerm();
			$list = getExcelArray($file);
			if(!isset($list)) {
				return getAjaxResp('上传的文件格式好像不对哎…');
			}
			$this->assign([
					'pre_view' => true,
					'term' => $term,
					'list' => $list,
			]);
			$key = timestamp();
			$data = [
					'data' => $list,
					'term' => $term,
			];
			Session::flash((string)$key, $data);
			return json([
					'success' => true,
					'msg' => 'success',
					'content' => $this->fetch('course/preview'),
					'key' => $key,
					'url' => url('index/course/save'),
			]);
		} else {
			$this->error();
		}
	}
}