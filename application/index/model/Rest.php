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
	
	public function addItem($term, $date1, $date2, $remark='') {
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
					'end' => $date2,
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
	
	public function apply($term, $id) {
		$item = $this->where('id', $id)->find();
		if($item['term'] != $term) {
			throw new MyException('数据异常，请重试', -659);
		}
		$st = $item['start'];
		$ed = $item['end'];
		$tm1 = Term::getDifferFromTerm($st);
		$tm2 = Term::getDifferFromTerm($ed);
		$m_table = new Table();
		$list = $m_table->where('term', $term)->order('num','asc')->select();
		$dat_all = [];
		$cnt = 0;
		foreach($list as $item) {
			if($item['num'] > $tm2) {
				break;
			}
			if($item['num'] >= $tm1 && $item['flag'] == 1) {
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
}