<?php

namespace app\index\model;

use think\Model;
use app\common\MyException;

class Rest extends Model {
	
	protected $pk = 'id';
	
	protected function initialize() {
 		parent::initialize();
	}
	
	public function getRestTimetable($term = '') {
		if(empty($term)) {
			return null;
		}
		return $this->where('term', $term)->select();
	}
	
	public function addItem($term, $date1, $date2, $sec1, $sec2, $remark='') {
		try {
// 			$all_dat = $this->where('term', $term)->select();
// 			$a_sec1 = strtotime($date1);
// 			$a_sec2 = strtotime($date2);
// 			foreach ($all_dat as $item) {
// 				$sec1 = strtotime($item['start']);
// 				$sec2 = strtotime($item['end']);
// 			}
			$this->data([
					'term' => $term,
					'start' => $date1,
					's_sec' => $sec1,
					'end' => $date2,
					'e_sec' => $sec2,
					'remark' => $remark,
			]);
			$this->save();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function getItem($term) {
		try {
			return $this->where('term', $term)->select();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function getEnabledItem($term) {
		try {
			return $this->where([
					'term'=>$term,
					'apply' => 1,
			])->select();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function apply($term, $id) {
		$item = $this->where('id', $id)->find();
		if($item['term'] != $term) {
			throw new MyException('数据异常，请重试', -659);
		}
		$st = $item['start'];
		$ed = $item['end'];
		$s_sec = $item['s_sec'];
		$e_sec = $item['e_sec'];
		$tm1 = Term::getDifferFromTerm($st);
		$tm2 = Term::getDifferFromTerm($ed);
		$m_table = new Table();
		$list = $m_table->where('term', $term)->order([
				'num' => 'asc',
				'section' => 'asc',
		])->select();
		$dat_all = [];
		$cnt = 0;
		foreach($list as $item) {
			if(($item['num'] == $tm2 && $item['section'] > $e_sec) || $item['num'] > $tm2) {
				break;
			}
			if($item['num'] >= $tm1 && $item['section'] >= $s_sec && $item['flag'] == 1) {
				$dat_item = [
						'id' => $item['id'],
						'place' => '',
						'dispatch' => 1,
						'flag' => 0,
				];
				array_push($dat_all, $dat_item);
				$cnt++;
			}
		}
		$m_table->saveAll($dat_all);
		$this->where('id', $id)
		->update([
					'apply' => 1,
		]);
		return $cnt;
	}
	public static function deleteItemByTerm($term) {
		try {
			db('rest')->where([
					'term' => $term,
			])->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
}