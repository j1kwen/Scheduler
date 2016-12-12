<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Exception;
use app\index\model\TypeList;

class Type extends BaseController {
	
	public function index() {
		$type = model('typeList');
		$this->assign([
				'title' => '课程类型管理',
		]);
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {			
			$type = new TypeList();
			$list = $type->getItemList();
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
			$name = $request->param('name');
			$description = $request->param('description', '');
			if(!empty($name)) {
				$type = model('typeList');
				try {
					$type->addItem($name, $description);
					return $this->getAjaxResp('success', true);
				} catch (Exception $e) {
					return $this->getAjaxResp('服务器错误，请重试！');
				}
			} else {
				return $this->getAjaxResp();
			}	
		} else {
			$this->error();
		}
	}
	
	public function modify() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			$name = $request->param('name');
			$description = $request->param('description');
			if(!empty($name)) {
				$type = model('typeList');
				try {
					$type->updateItem($_id, $name, $description);
					return $this->getAjaxResp('success', true);
				} catch (Exception $e) {
					return $this->getAjaxResp('服务器错误，请重试！');
				}
			} else {
				return $this->getAjaxResp('参数错误！');
			}
		} else {
			$this->error();
		}
	}
}