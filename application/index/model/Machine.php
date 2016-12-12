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
	
	public function addItem($name, $no, $mac, $type) {
		try {
			$this->data([
					'name' => $name,
					'no' => $no,
					'mac' => $mac,
					'type' => $type,
			]);
			$this->save();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function updateItem($id, $mac, $type) {
		try {
			$this->where('id',$id)
			->update([
					'mac' => $mac,
					'type' => $type,
			]);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function deleteItem($id) {
		try {			
			$this->where('id',$id)
			->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
}