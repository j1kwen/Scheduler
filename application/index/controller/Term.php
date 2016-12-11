<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use function think\error;
use think\Exception;

class Term extends BaseController {
	
	public function index() {
		$term = model('term');
		$this->assign([
				'title' => '学期管理',
		]);
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {			
			$term = model('term');
			$list = $term->order([
					'year' => 'desc',
					'term' => 'desc',
			])->select();
			$this->assign([
					'list' => $list,
			]);
			return $this->fetch();
		} else {
			$this->error();
		}
	}
	
	public function add() {
		$request = Request::instance();
		if($request->isAjax()) {
			$year = $request->param('year');
			$term_ = $request->param('term');
			$date_ = $request->param('date');
			if(!empty($year) && !empty($term_) && !empty($date_)) {
				$term = model('term');
				try {
					$term->data([
							'year' => $year,
							'term' => $term_,
							'start' => $date_,
							'code' => $year.'0'.$term_,
							'is_cur' => 0,
					]);
					$term->save();
					return $this->getAjaxResp("success", true);
				} catch (Exception $e) {
					$e_msg = $e->getData()["PDO Error Info"]["Driver Error Code"];
					if($e_msg == "1062") {						
						return $this->getAjaxResp("该学期已存在！");
					} else {
						return $this->getAjaxResp("未知错误，请稍候重试！");
					}
				}
			} else {
				return $this->getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function del() {

		$request = Request::instance();
		if($request->isAjax()) {
			$s_id = $request->param('id','');
			if(!empty($s_id)) {
				$term = model('term');
				try {
					$term->where('id',$s_id)
					->delete();
					
					// TODO: delete other info about this term
					
					return $this->getAjaxResp("success", true);
				} catch (Exception $e) {
					return $this->getAjaxResp("服务器异常，请稍后重试！");
				}
			} else {
				return $this->getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function cur() {
		$request = Request::instance();
		if($request->isAjax()) {
			$s_id = $request->param('id','');
			if(!empty($s_id)) {
				$term = model('term');
				try {
					$term->where('is_cur',1)
					->update([
							'is_cur' => 0,
					]);
					$term->where('id',$s_id)
						->update([
								'is_cur' => 1,
						]);
					return $this->getAjaxResp("success", true);
				} catch (Exception $e) {
					return $this->getAjaxResp("服务器异常，请稍后重试！");
				}
			} else {
				return $this->getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function modify() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id', null);
			$_date = $request->param('date', null);
			if(!empty($_id) && !empty($_date)) {
				$term = model('term');
				try {
					$term->where('id', $_id)
					->update([
							'start' => $_date,
					]);
					return $this->getAjaxResp("success", true);
				} catch (Exception $e) {
					return $this->getAjaxResp("服务器异常，请稍后重试！");
				}
			} else {
				return $this->getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
}