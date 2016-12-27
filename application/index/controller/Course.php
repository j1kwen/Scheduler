<?php

namespace app\index\controller;

use app\index\model\Term;
use think\Request;
use think\View;
use think\Session;
use think\Exception;
use app\index\model\TypeList;
use app\common\MyException;

class Course extends BaseController {
	
	public function index() {
		$term = Term::getCurTerm();
		if(isset($term)) {
			$alert_string = (new View())->assign([
					'type' => 'success',
					'close' => false,
					'title' => '学期已设置！',
					'body' => '现在是<strong>'.$term['year'].'-'.($term['year']+1).'</strong>学年 第<strong>'.$term['term'].'</strong>学期',
					'href' => url('index/term/index'),
					'href_text' => '切换学期',
			])->fetch('public/alert');
		} else {
			// no term
			$alert_string = (new View())->assign([
					'type' => 'danger',
					'close' => false,
					'title' => '错误！',
					'body' => '暂未设置当前学期，无法查看和导入课程！',
					'href' => url('index/term/index'),
					'href_text' => '立即设置',
			])->fetch('public/alert');
		}
		$this->assign([
				'term' => $term,
				'title' => '课程管理',
				'alert' => $alert_string,
		]);
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			$cor = model('course');
			$term = Term::getCurTerm();
			$list = null;
			if(isset($term)) {
				$list = $cor->getItem($term['code']);
				$m_typeList = new TypeList();
				$type = $m_typeList->getItemList();
				foreach ($list as $i) {
					foreach ($type as $j) {
						if($j->id == $i->type) {
							$i->tName = $j->name;
							break;
						}
					}
				}
			}
			$this->assign([
					'term' => $term,
					'list' => $list,
			]);
			return $this->fetch();
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
					$cor = model('course');
					$cor->deleteItem($_id);
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
					$cor = model('course');
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
					$cor = model('course');
					$cor->importItems($list, $term['code']);
					return json([
						'success' => true,
						'msg' => '数据导入成功！',
					]);
				} catch (MyException $em) {					
					return getAjaxResp($em->getData('msg'));
				} catch(Exception $e) {
					$e_msg = $e->getData()["PDO Error Info"]["Driver Error Code"];
					if($e_msg == "1062") {
						return getAjaxResp("有些课这学期好像已经导入了，请检查后重试！");
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
}