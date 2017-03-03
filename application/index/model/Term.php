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
			$itm = $this->where('id', $id)->find();
			$term = $itm['code'];
			$this->where('id',$id)
				->delete();
			Course::deleteItemByTerm($term);
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
	
	private function diffBetweenTwoDays ($day1, $day2)
	{
	  $second1 = strtotime($day1);
	  $second2 = strtotime($day2);
	  return floor(($second2 - $second1) / 86400);
	}
	
	public function getCurrentDays() {
		$cor = $this->where('is_cur', 1)->find();
		if(empty($cor)) {
			return -1;
		}
		$day1 = $cor['start'];
		$day2 = date('Y-m-d',time());
		return $this->diffBetweenTwoDays($day1, $day2) + 1;
	}
	
	public static function getDifferFromTerm($day2) {
		$cor = Term::getCurTerm();
		if(empty($cor)) {
			return -1;
		}
		$day1 = $cor['start'];
		$second1 = strtotime($day1);
		$second2 = strtotime($day2);
		return floor(($second2 - $second1) / 86400) + 1;
	}
	
	public static function getCurTerm() {
		return db('term')->where('is_cur', 1)->find();
	}
}