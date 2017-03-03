<?php
namespace app\index\model;

use think\Model;
use app\common\MyException;
use think\Config;

class Table extends Model {
	
	protected $table = 'schedule';
	protected $pk = 'id';
	
	protected function initialize() {
		parent::initialize();
			
	}
	
	public function initTable($term, $items) {
		try {
			$datAll = [];
			foreach ($items as $item) {
				$dat = [];
				$week = $item['week'];
				$day = $item['day'];
				$section = $item['section'];
				for($i = 1; $i < 30; $i++) {
					$ts = $week & (1 << $i);
					if($ts == 0) {
						continue;
					}
					// this week $i ok
					for($j = 1; $j < 15 ; $j += 2) {
						$ts2 = $section & (1 << $j);
						if($ts2 == 0) {
							continue;
						}
						// section $j ok
						$dat['rela'] = $item['code'];
						$dat['term'] = $term;
						$dat['week'] = $i;
						$dat['day'] = $day;
						$dat['num'] = ($i - 1) * 7 + $day;
						$dat['section'] = $j;
						$dat['course'] = $item['no'];
						$dat['place'] = '';
						$dat['flag'] = 0;
						array_push($datAll, $dat);
					}
				}
			}
			return $this->saveAll($datAll);
		} catch (\think\Exception $e) {
			throw new MyException('初始化课表失败！', -563);
		}
	}
	
	public function getSingleItem($id) {
		return $this->where('id', $id)->find();
	}
	
	public function getItem($term, $week) {
		$ret = [];
		$mac_arr = [];
		$m_machine = new Machine();
		$machine = $m_machine->where([])->select();
		foreach ($machine as $mch) {
			$mac_arr[$mch['id']] = [
					'name' => $mch['name'],
					'cls' => '',
					'csid' => 0,
					'rm' => '',
					'id' => 0,
			];
		}
		for($day = 1; $day <= 7 ; $day++) {
			$arr_dat_day = [];
			for($section = 1; $section < 10 ; $section += 2) {
				$mac_cp = $mac_arr;
				$arr_dat_day[$section] = $mac_cp;
			}
			$items = $this->where([
					'term' => $term,
					'week' => $week,
					'day' => $day,
					'flag' => 1,
			])->select();
			foreach ($items as $item) {
				$arr = explode(',', $item['place']);
				$sec = $item['section'];
				foreach ($arr as $pi) {
					$arr_dat_day[$sec][$pi]['cls'] = $item['course'];
					$arr_dat_day[$sec][$pi]['csid'] = $term.$item['course'];
					$arr_dat_day[$sec][$pi]['id'] = $item['id'];
					$m_term = new Term();
					$now_d = $m_term->getCurrentDays();
					if($now_d >= $item['num']) {
						
						$arr_dat_day[$sec][$pi]['rm'] = 'disabled';
					}
				}
			}
			array_push($ret, $arr_dat_day);
		}
		return $ret;
	}
	
	public static function deleteItemByPlan($codes) {
		try {
			db('schedule')->where([
					'rela' => $codes,
			])->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public static function deleteItemByCourse($term, $course) {
		try {
			db('schedule')->where([
					'term' => $term,
					'course' => $course,
			])->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public static function deleteItemByTerm($term) {
		try {
			db('schedule')->where([
					'term' => $term,
			])->delete();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function modify($id, $week, $day, $section, $mac) {
		try {
			$term = Term::getCurTerm();
			$old = $this->where('id', $id)->find();
			$arr = explode(',', $mac);
			$cnt = 0;
			$cor = db('course')->where('code', $term['code'].$old['course'])->find();
			$total = $cor['c_stu'];
			foreach ($arr as $ai ) {
				$item = db('machine')->where('id', $ai)->find();
				$cnt = $item['mac'];
			}
			if($cnt <= $total) {
				throw new MyException('所选机房不足以容纳该班学生', -666);
			}
			
			$this->where('id', $id)
			->update([
					'week' => $week,
					'day' => $day,
					'num' => ($week-1) * 7 + $day,
					'section' => $section,
					'place' => $mac,
					'dispatch' => 1,
					'flag' => 1,
			]);
			return $this->where('id', $id)->find();
		} catch (\think\Exception $e) {
			throw $e;
		}
	}
	
	public function getIg($term) {
		return $list = $this->where([
				'dispatch' => 1,
				'flag' => 0,
		])->select();
	}
	
	public function getStatus($term) {
		$list = $this->where([
				'term' => $term,
				'flag' => 1,
		])->find();
		return !empty($list);
	}
	
	public function doWorkList($term) {
		$rate_h = 0.9;
		$rate_l = 0.5;
		$cfg = Config::get('rule');
		$m_machine = new Machine();
		$m_course = new Course();
		$m_plan = new Plan();
		$m_rest = new Rest();
		$all_mac = $m_machine->where([])->select();
		$all_dat = [];
		// week
		for($week = 1; $week < 30; $week++) {
			// day
			for($day = 1; $day <= 7; $day++) {
				// section
				for($section = 1; $section < 10; $section += 2) {
					
					$items = $this->where([
							'term' => $term,
							'week' => $week,
							'day' => $day,
							'section' => $section,
					])->select();
					if(empty($items)) {
						continue;
					}
					$temp = [];
					foreach($all_mac as $mac_i) {
						$temp[$mac_i['id']] = $mac_i['mac'];
					}
					foreach ($items as $row) {
						//get plan info
						$rela = $row['rela'];
						$plan = $m_plan->where('code', $rela)->find();
						// get course info
						$belong = $plan['term'].$plan['no'];
						$course = $m_course->where('code', $belong)->find();
						// get type
						$tp = $course['type'];
				
						//get config
						$arr = explode(',', $cfg[$tp]);
						
						$stu_num = $course['c_stu'];
						
						$rate_all = 0;
						$depter_i = -1;
						$depter_j = -1;
						$fis = false;
						foreach ($arr as $tpi) {
							// get machine for this item can be saved
							$machine = $m_machine->where('type', $tpi)->select();
							$len = count($machine);
							for($i = 0; $i< $len; $i++) {
								$cnt = 0;
								$i_gp = $machine[$i]['gp'];
								for($j = $i; $j < $len ; $j++) {
									$j_gp = $machine[$j]['gp'];
									if($i_gp != $j_gp || $temp[$machine[$j]['id']] == 0) {
										break;
									}
									$cnt += $machine[$j]['mac'];
									if($cnt >= $stu_num) {
										$t_rate_all = $stu_num / $cnt;
										if($t_rate_all >= $rate_l
											&& $t_rate_all <= $rate_h
											&& $t_rate_all > $rate_all) {
											$rate_all = $t_rate_all;
											$depter_i = $i;
											$depter_j = $j;
										}
										break;
									}
								}
							}
							if($depter_i != -1) {
								// doit
								$str = '';
								for($i = $depter_i; $i <= $depter_j; $i++) {
									if($i != $depter_i) {
										$str.=',';
									}
									$str.=$machine[$i]['id'];
									$temp[$machine[$i]['id']] = 0;
								}
								
								$lst = [
										'id' => $row['id'],
										'place' => $str,
										'flag' => 1,
								];
								
								$fis = true;
								break;
							}
						}
						if(!$fis && $depter_i != -1) {
							$str = '';
							for($i = $depter_i; $i <= $depter_j; $i++) {
								if($i != $depter_i) {
									$str.=',';
								}
								$str.=$machine[$i]['id'];
								$temp[$machine[$i]['id']] = 0;
							}
							
							$lst = [
									'id' => $row['id'],
									'place' => $str,
									'flag' => 1,
							];
							
						} else if(!$fis && $depter_i == -1) {
							// no answer
							$lst = [
									'id' => $row['id'],
									'dispatch' => 1,
									'flag' => 0,
							];
						}
						if(!empty($lst)) {
							array_push($all_dat, $lst);
						}
					}
				}
			}
		}
		
		return $this->saveAll($all_dat);
		
// 		foreach ($items as $row) {
// 			// get plan info
// 			$rela = $row['rela'];
// 			$plan = $m_plan->where('code', $rela)->find();
// 			// get course info
// 			$belong = $plan['term'].$plan['no'];
// 			$course = $m_course->where('code', $belong)->find();
// 			// get type
// 			$tp = $course['type'];
			
// 			//get config
// 			$arr = explode(',', $cfg[$tp]);
			
// 			foreach ($arr as $tpi) {
// 				// get machine for this item can be saved
// 				$machine = $m_machine->where('type', $tpi)->select();
// 				foreach($machine as $mac) {
// 					$m_id = $mac['id'];
					
// 					for()
// 				}
// 			}
// 		}
// 		$this->saveAll($items);
	}
}