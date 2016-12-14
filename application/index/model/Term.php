<?php

namespace app\index\model;

use think\Model;

class Term extends Model {
	
	protected $pk = 'id';
	
	protected function initialize() {
 		parent::initialize();
 		
	}
	
	public function getItemList() {
		return $this->order([
				'year' => 'desc',
				'term' => 'desc',
		])->select();
	}
	
	public function addItem($year, $term, $start) {
		try {
			$this->data([
					'year' => $year,
					'term' => $term,
					'start' => $start,
					'code' => $year.'0'.$term,
					'is_cur' => 0,
			]);
			$this->save();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function deleteItem($id) {
		try {			
			$this->where('id',$id)
				->delete();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function setCurrent($id) {
		try {
			$this->where('is_cur',1)
			->update([
					'is_cur' => 0,
			]);
			$this->where('id',$id)
			->update([
					'is_cur' => 1,
			]);			
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function updateItem($id, $start) {
		try {
			$this->where('id', $id)
			->update([
					'start' => $start,
			]);
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function getCurrentTerm() {
		return $this->where('is_cur', 1)->find();
	}
	
	public static function getCurTerm() {
		return db('term')->where('is_cur', 1)->find();
	}
}