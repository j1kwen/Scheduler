<?php

namespace app\index\controller;

use app\index\model\Term;
use think\View;
use think\Request;
use think\Session;
use app\common\MyException;
use think\Exception;

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
					'title' => '本学期课程已导入！',
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
				try {					
					$list = $plan->getItem($term['code']);
				} catch (MyException $e) {
					return $this->fetch('public/error');
				}
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
					'content' => $this->fetch('plan/preview'),
					'key' => $key,
					'url' => url('index/plan/save'),
			]);
		} else {
			$this->error();
		}
	}
	
	public function save() {
		$request = Request::instance();
		if($request->isAjax()) {
			$tsp = $request->param('key');
			$has_title = $request->param('titled', false);
			$ids = $request->param('except');
			if(Session::has($tsp)) {
				$data = Session($tsp);
				$list = $data['data'];
				$term = $data['term'];
				if($has_title == 'true') {
					unset($list[0]);
				}
				if(!empty($ids)) {
					$list_id = explode(',',$ids);
					foreach($list_id as $i) {
						if(isset($list[$i])) {
							unset($list[$i]);
						}
					}
				}
				try {
					$cor = model('plan');
					$cor->importItems($list, $term['code']);
					return json([
							'success' => true,
							'msg' => '数据导入成功！',
					]);
				} catch (MyException $em) {
					return getAjaxResp($em->getData('msg'));
				} catch(Exception $e) {
					if(isset($e->getData()["PDO Error Info"])) {						
						$e_msg = $e->getData()["PDO Error Info"]["Driver Error Code"];
						if($e_msg == "1062") {
							return getAjaxResp("本学期好像已经安排了同一课程的同一上课时间，请检查后重试！");
						} else {
							return getAjaxResp("未知错误，请稍候重试！");
						}
					} else {
						return getAjaxResp("未知错误，请稍候重试！");
					}
				}
			}
			return getAjaxResp('数据导入失败！请重试！');
		} else {
			$this->error();
		}
	}
	
	public function del() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			if(!empty($_id)) {
				try {
					$cor = model('plan');
					$cor->deleteItem($_id);
					// TODO: del
	
					return getAjaxResp("success",true);
				} catch (Exception $e) {
					return getAjaxResp("服务器异常，请稍候重试！");
				}
			} else {
				return getAjaxResp();
			}
		} else {
			$this->error();
		}
	}
	
	public function muldel() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			if(!empty($_id)) {
				try {
					$cor = model('plan');
					$cor->deleteMultiItems($_id);
					// TODO: del
	
					return getAjaxResp("success",true);
				} catch (Exception $e) {
					return getAjaxResp("服务器异常，请稍候重试！");
				}
			} else {
				return getAjaxResp();
			}
		} else {
			$this->error();
		}
	}
}