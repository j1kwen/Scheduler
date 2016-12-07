<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\TypeList;

class Machine extends Controller
{

	public function index()
	{
		$request = Request::instance();
 		$mach = model('machine');
 		if($request->isPost()) {
 			$add_name = $request->param('name');
 			$add_no = $request->param('no');
 			$add_mac = $request->param('mac');
 			$add_type = $request->param('type');
 			if(!empty($add_name) && !empty($add_no) && !empty($add_mac) && !empty($add_type)) {
 				$isadd = true;
 				$mach->data([
 						'name' => $add_name,
 						'no' => $add_no,
 						'mac' => $add_mac,
 						'type' => $add_type,
 				]);
 				$mach->save();
 			}
 		}
		$list = $mach->order('no','asc')->select();
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
				'title' => '机房管理',
				'list' => $list,
				'types' => $type,
				'isadd' => isset($isadd) ? $isadd : false,
				'add_name' => isset($add_name) ? $add_name : '',
		]);
		
		return $this->fetch();
	}
	
	public function type() {
		
		$request = Request::instance();
		$isadd = false;
		$name = '';
		$type = model('typeList');
		if($request->isPost()) {
			$name = $request->param('name');
			$description = $request->param('description','');
			if(!empty($name)) {
				$isadd = true;
				$type->data([
						'name' => $name,
						'description' => $description,
				]);
				$type->save();
			}
		}
		$list = $type->order('id','asc')->select();
		$this->assign([
				'title' => '课程类型管理',
				'list' => $list,
				'isadd' => $isadd,
				'add_name' => $name,
		]);
		return $this->fetch();
	}
}