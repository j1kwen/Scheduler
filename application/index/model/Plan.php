<?php

namespace app\index\model;

use think\Model;
use think\Exception;
use app\common\MyException;

class Plan extends Model {
	
	protected $pk = 'id';
	
	protected function initialize() {
		parent::initialize();
	}
	
	public function getItem($term = null) {
		if(isset($term)) {
			return $this->where('term', $term)->select();
		}
		return null;
	}
	
	public function deleteItem($id) {
		try {
			$this->where('id',$id)
			->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function deleteMultiItems($ids) {
		try {
			$this->destroy($ids);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function importItems($list, $term) {
// 		try {
// 			$types = new TypeList();
// 			$t_lst = $types->getItemList();
// 			$t_rep = [];
// 			foreach($t_lst as $t_row) {
// 				$t_rep[$t_row['name']] = $t_row['id'];
// 			}
// 			$datAll = [];
// 			foreach($list as $row) {
// 				if(count($row) != count($this->field) - 2) {
// 					throw new MyException('导入的文件好像有问题…', -1);
// 				}
// 				$dat = [];
// 				foreach($row as $k=>$cell) {
// 					if($k == 5) {
// 						$dat[$this->field[$k]] = $t_rep[$cell];
// 					} else {
// 						$dat[$this->field[$k]] = $cell;
// 					}
// 					if($k == 10) {
// 						$dat[$this->field[$k+1]] = $term;
// 						$dat[$this->field[$k+2]] = $term.$row[0];
// 					}
// 				}
// 				array_push($datAll, $dat);
// 			}
// 			return $this->saveAll($datAll);
// 		} catch (\think\Exception $e) {
// 			throw $e;
// 		}
	}
}