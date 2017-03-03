<?php

namespace app\index\model;

use think\Model;
use think\Exception;

class Machine extends Model {
	
	protected $pk = 'id';
	
	protected function initialize() {
 		parent::initialize();
 		
	}
	
	public function getItemList() {
		return $this->order('no','asc')->select();
	}
	
	public function addItem($name, $no, $mac, $type, $gp) {
		try {
			$this->data([
					'name' => $name,
					'no' => $no,
					'mac' => $mac,
					'type' => $type,
					'gp' => $gp,
			]);
			$this->save();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function updateItem($id, $mac, $type, $gp) {
		try {
			$this->where('id',$id)
			->update([
					'mac' => $mac,
					'type' => $type,
					'gp' => $gp,
			]);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function deleteMultiItems($ids) {
		try {
			$arr = explode(',', $ids);
			foreach ($arr as $id) {
				model('table')->where('place', $id)
				->update([
						'place' => '',
				]);
				$this->where('id', $id)->delete();
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function getMachineName($id) {
		$item = $this->where('id', $id)->find();
		return $item['name'];
	}
	
	public function getMachineIds($names) {
		$arr = explode(',', $names);
		$ret = '';
		$cnt = 0;
		foreach ($arr as $ai) {
			$item = $this->where('name', $ai)->find();
			if($cnt > 0) {
				$ret.=',';
			}
			$cnt++;
			$ret.=$item['id'];
		}
		return $ret;
	}
	public function deleteItem($id) {
		try {
			model('table')->where('place', $id)
			->update([
					'place' => '',
			]);
			$this->where('id',$id)
			->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
}