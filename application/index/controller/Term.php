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
			$list = $term->getItemList();
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
					$term->addItem($year, $term_, $date_);
					return getAjaxResp("success", true);
				} catch (Exception $e) {
					$e_msg = $e->getData()["PDO Error Info"]["Driver Error Code"];
					if($e_msg == "1062") {						
						return getAjaxResp("该学期已存在！");
					} else {
						return getAjaxResp("未知错误，请稍候重试！");
					}
				}
			} else {
				return getAjaxResp("param error");
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
					$term->deleteItem($s_id);
					
					// TODO: delete other info about this term
					
					return getAjaxResp("success", true);
				} catch (Exception $e) {
					return getAjaxResp("服务器异常，请稍后重试！");
				}
			} else {
				return getAjaxResp("param error");
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
					$term->setCurrent($s_id);
					return getAjaxResp("success", true);
				} catch (Exception $e) {
					return getAjaxResp("服务器异常，请稍后重试！");
				}
			} else {
				return getAjaxResp("param error");
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
					$term->updateItem($_id, $_date);
					return getAjaxResp("success", true);
				} catch (Exception $e) {
					return getAjaxResp("服务器异常，请稍后重试！");
				}
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
}