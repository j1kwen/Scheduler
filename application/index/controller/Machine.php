<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\TypeList;
use think\Exception;

class Machine extends BaseController {
	
	public function index()
	{
		$request = Request::instance();
		$type = TypeList::all(function($query) {
			$query->order('id', 'asc');
		});
		$this->assign([
				'title' => '机房管理',
				'types' => $type,
		]);
		
		return $this->fetch();
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			$mach = model('machine');
			$list = $mach->order('name','asc')->select();
			$type = TypeList::all(function($query) {
				$query->order('id', 'asc');
			});
			foreach ($list as $i) {
				foreach ($type as $j) {
					if($j->id == $i->type) {
						$i->tName = $j->name;
						break;
					}
				}
			}
			$this->assign([
					'list' => $list,
					'types' => $type,
			]);
			return $this->fetch();
		} else {
			$this->error();
		}
	}
	
	public function add() {
		$request = Request::instance();
		if($request->isAjax()) {			
			$add_name = $request->param('name');
			$add_no = $request->param('no');
			$add_mac = $request->param('mac');
			$add_type = $request->param('type');
			if(!empty($add_name) && !empty($add_no) && !empty($add_mac) && !empty($add_type)) {
				try {
					$mach = model('machine');
					$mach->data([
							'name' => $add_name,
							'no' => $add_no,
							'mac' => $add_mac,
							'type' => $add_type,
					]);
					$mach->save();
					return $this->getAjaxResp("success", true);
				} catch (Exception $e) {
					$e_msg = $e->getData()["PDO Error Info"]["Driver Error Code"];
					if($e_msg == "1062") {						
						return $this->getAjaxResp("机房号或预约编号已存在！");
					} else {
						return $this->getAjaxResp("未知错误，请稍候重试！");
					}
				}
			} else {
				return $this->getAjaxResp();
			}
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
					$mach = model('machine');
					$mach->where('id',$_id)
						->delete();
					// TODO: del
					
					return $this->getAjaxResp("success",true);
				} catch (Exception $e) {
					return $this->getAjaxResp("服务器异常，请稍候重试！");
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
			$_mac = $request->param('mac');
			$_type = $request->param('type');
			if(!empty($_id) && !empty($_mac) && !empty($_type)) {
				try {
					$mach = model('machine');
					$mach->where('id',$_id)
					->update([
							'mac' => $_mac,
							'type' => $_type,
					]);	
					return $this->getAjaxResp("success",true);
				} catch (Exception $e) {
					return $this->getAjaxResp("服务器异常，请稍候重试！");
				}
			} else {
				return $this->getAjaxResp();
			}
		} else {
			$this->error();
		}
	}

}