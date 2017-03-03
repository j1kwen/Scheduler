<?php
namespace app\index\model;

use think\Model;
use think\Exception;
use app\common\MyException;

class Course extends Model {
	
	protected $pk = 'id';
	protected $field = ['no','col','pro','cls','name','type','c_stu','c_class','teach_col','teach_name','teach_tel','term','code'];
	
	protected function initialize() {
		parent::initialize();
	}
	
	public function getItem($term = null) {
		if(isset($term)) {
			return $this->where('term', $term)->select();
		}
		return null;
	}
	
	public function getDetails($code) {
		return $this->where('code', $code)->find();
	}
	
	public function getCourseBaseInfo($term) {
		if(isset($term)) {
			$list = $this->getItem($term);
			$ret = [];
			foreach ($list as $item) {
				$ret[$item['no']] = [
						'name' => $item['name'],
						'col' => $item['col'],
						'teach_name' => $item['teach_name'],
				];
			}
			return $ret;
		}
		return null;
	}
	
	public function deleteItem($id) {
		try {
			$cor = $this->where('id', $id)->find();
			$term = $cor['term'];
			$no = $cor['no'];
			$this->where('id',$id)
			->delete();
			Plan::deleteItemByCourse($term, $no);
			
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public static function deleteItemByTerm($term) {
		try {
			db('course')->where([
					'term' => $term,
			])->delete();
			Plan::deleteItemByTerm($term);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function deleteMultiItems($ids) {
		try {
			$arr = explode(',', $ids);
			foreach ($arr as $id) {
				$cor = $this->where('id', $id)->find();
				$term = $cor['term'];
				$no = $cor['no'];
				Plan::deleteItemByCourse($term, $no);
				$this->where('id', $id)->delete();
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function importItems($list, $term) {
		try {
			$types = new TypeList();
			$t_lst = $types->getItemList();
			$t_rep = [];
			foreach($t_lst as $t_row) {
				$t_rep[$t_row['name']] = $t_row['id'];
			}
			$datAll = [];
			foreach($list as $row) {
				if(count($row) != count($this->field) - 2) {
					throw new MyException('导入的文件好像有问题…', -1);
				}
				$dat = [];
				foreach($row as $k=>$cell) {
					if($k == 5) {
						$dat[$this->field[$k]] = $t_rep[$cell];
					} else {
						$dat[$this->field[$k]] = $cell;
					}
					if($k == 10) {
						$dat[$this->field[$k+1]] = $term;
						$dat[$this->field[$k+2]] = $term.$row[0];
					}
				}
				array_push($datAll, $dat);
			}
			return $this->saveAll($datAll);
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
}