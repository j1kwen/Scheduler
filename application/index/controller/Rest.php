<?php
namespace app\index\controller;

use app\common\MyException;
use app\index\model\Term;
use think\Controller;
use think\error;
use think\Request;

class Rest extends Controller {
	
	public function index() {
		$this->assign([
				
		]);
		return $this->fetch();
	}
	
	public function add() {
		$request = Request::instance();
		if($request->isAjax()) {
			$date_1 = $request->param('date1');
			$date_2 = $request->param('date2');
			$section_1 = $request->param('section1');
			$section_2 = $request->param('section2');
			$remark = $request->param('description','');
			if(!empty($date_1) && !empty($date_2) && !empty($section_1) && !empty($section_2)) {
				$sec1 = strtotime($date_1);
				$sec2 = strtotime($date_2);
				if($sec1 > $sec2 || ($sec1 == $sec2 && $section_1 > $section_2)) {
					return getAjaxResp("所输入起始日期（节次）晚于终止日期（节次）！");
				}
				$term = Term::getCurTerm();
				if(!isset($term)) {
					return getAjaxResp("未设置学期！");
				}
				$rest = model('rest');
				try {
					$rest->addItem($term['code'], $date_1, $date_2, $section_1, $section_2, $remark);
					return getAjaxResp("success", true);
				} catch (MyException $e) {
					return getAjaxResp($e->getData('msg'));
				}
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			$term = Term::getCurTerm();
			$rest = model('rest');
			$list = null;
			if(isset($term)) {				
				$list = $rest->getItem($term['code']);
			}
			if(isset($item)) {
				$d_term = date_create($item->start);
				$d_now = date_create();
				if(date_timestamp_get($d_now) >= date_timestamp_get($d_term)) {
					$differ = floor(date_diff($d_term, $d_now)->format('%a') / 7) + 1;
				}
			}
			$this->assign([
					'list' => $list,
					'term' => $term,
			]);
			return $this->fetch();
		} else {
			$this->error();
		}
	}
	
	public function apply() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			$term = Term::getCurTerm();
			$rest = model('rest');
			if(isset($term)) {
				try {					
					$cnt = $rest->apply($term['code'], $_id);
					return json([
							'success' => true,
							'msg' => '成功应用该时段，'. $cnt .'项课程受到影响。',
					]);
				} catch (MyException $e) {
					return getAjaxResp($e->getData('msg'));
				}
			}
			return getAjaxResp("未设置学期！");
		} else {
			$this->error();
		}
	}
}