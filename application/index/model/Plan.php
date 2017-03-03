<?php

namespace app\index\model;

use think\Model;
use think\Exception;
use app\common\MyException;
use app\index\model\Course;

class Plan extends Model {
	
	protected $pk = 'id';
	protected $field = ['no','name','week','day','section','term','code'];
	protected $d_rep = ['', '一', '二', '三', '四', '五', '六', '日'];
	
	protected function initialize() {
		parent::initialize();
	}
	
	public function getItem($term = null) {
		if(isset($term)) {
			$list = $this->where('term', $term)->select();
			$course = (new Course())->getCourseBaseInfo($term);
			if(!empty($course)) {
				foreach($list as $item) {
					$no = $item['no'];
					$item['c_name'] = $course[$no]['name'];
					$item['c_col'] = $course[$no]['col'];
					$item['c_teacher'] = $course[$no]['teach_name'];
					$item['week'] = integerToInterval($item['week']);
					$item['section'] = integerToInterval($item['section']);
					$item['day'] = '周'.$this->d_rep[$item['day']];
				}
				return $list;
			} else {
				throw new MyException('学期或课程信息错误！',-1);
			}
		}
		return null;
	}
	
	public function deleteItem($id) {
		try {
			$cor = $this->where('id', $id)->find();
			$codes = $cor['code'];
			$this->where('id',$id)
			->delete();
			Table::deleteItemByPlan($codes);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public static function deleteItemByCourse($term, $course) {
		try {
			db('plan')->where([
					'term' => $term,
					'no' => $course,
			])->delete();
			Table::deleteItemByCourse($term, $course);
		} catch (Exception $e) {
			throw $e;
		}
	}
	public static function deleteItemByTerm($term) {
		try {
			db('plan')->where([
					'term' => $term,
			])->delete();
			Table::deleteItemByTerm($term);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function deleteMultiItems($ids) {
		try {
			$arr = explode(',', $ids);
			foreach ($arr as $id) {
				$cor = $this->where('id', $id)->find();
				$codes = $cor['code'];
				Table::deleteItemByPlan($codes);
				$this->where('id', $id)->delete();
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function importItems($list, $term) {
		try {
			$datAll = [];
			$course = (new Course())->getCourseBaseInfo($term);
			foreach($list as $row) {
				if(count($row) != count($this->field) - 2) {
					throw new MyException('导入的文件好像有问题…', -1);
				}
				if(empty($course[$row[0]])) {
					throw new MyException('表中某些课程在本学期课程中不存在！', -2);
				}
				$dat = [];
				foreach($row as $k=>$cell) {
					if($k == 1) {
						continue;
					}
					if($k == 2 || $k == 4) {
						$dat[$this->field[$k]] = intervalToInteger($cell);
						if($k == 4) {
							$dat[$this->field[$k+1]] = $term;
							$dat[$this->field[$k+2]] = $term.$row[0].'-'.$dat['week'].'-'.$dat['day'].'-'.$dat['section'];
						}
						continue;
					}
					$dat[$this->field[$k]] = $cell;
				}
				array_push($datAll, $dat);
			}
			// init table
			$table = new Table();
			$table->initTable($term, $datAll);
			return $this->saveAll($datAll);
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
}