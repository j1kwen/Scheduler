<?php

namespace app\index\model;

use think\Model;
use think\Exception;

class TypeList extends Model {
	
	protected $pk = 'id';
	
	protected function initialize() {
 		parent::initialize();
 		
	}
	/**
	 * 获取类型列表
	 */
	public function getItemList() {
		return $this->order('id','asc')->select();
	}
	
	/**
	 * 添加条目
	 * @param string $name
	 * @param string $description
	 */
	public function addItem($name, $description) {
		try {			
			$this->data([
					'name' => $name,
					'description' => $description,
			]);
			$this->save();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function updateItem($id, $name, $description) {
		try {			
			$this->where('id', $id)
			->update([
					'name' => $name,
					'description' => $description,
			]);
		} catch (Exception $e) {
			throw $e;
		}
	}
}