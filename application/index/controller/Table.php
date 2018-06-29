<?php
namespace app\index\controller;

use think\Controller;
use think\View;
use think\Model;
use think\Request;
use function think\error;
use app\index\model\Term;
use app\index\model\Machine;
use app\common\MyException;

class Table extends BaseController {
	
	public function index() {

		$this->assign('data',array(
			array(),
			array(),
		));
		$term = model('term');
		$table = model('table');
		$item = $term->where('is_cur',1)->find();
		$seted = true;
		$ig_list = [];
		if(isset($item)) {
			$d_term = date_create($item->start);
			$d_now = date_create();
			if(date_timestamp_get($d_now) >= date_timestamp_get($d_term)) {
				$differ = floor(date_diff($d_term, $d_now)->format('%a') / 7) + 1;
			}
			if(!$table->getStatus($item->code)) {
				$alert_string = (new View())->assign([
					'type' => 'warning',
					'close' => false,
					'title' => '注意！',
					'body' => '该学期暂未执行排课工作',
					'href' => 'javascript:void(0);',
					'ext_cls' => 'start-working',
					'href_text' => '立即开始排课',
				])->fetch('public/alert');
			} else {
				$ig_list = $table->getIg($item->code);
				$alert_string = (new View())->assign([
						'type' => 'warning',
						'close' => false,
						'title' => '注意！',
						'body' => '课程安排存在互斥情况，部分课程未安排，请手动安排。',
						'href' => url('index/table/mutex'),
						'ext_cls' => '',
						'href_text' => '查看这些课程',
				])->fetch('public/alert');
			}
		} else {
			$alert_string = (new View())->assign([
					'type' => 'danger',
					'close' => false,
					'title' => '错误！',
					'body' => '暂未设置当前学期，无法查看课表！',
					'href' => url('index/term/index'),
					'href_text' => '立即设置',
			])->fetch('public/alert');
			$seted = false;
		}
		$this->assign([
				'title' => '课表总览',
				'cols' => 4,
				'rows' => 6,
				'week' => isset($differ)?$differ:null,
				'alert' => isset($alert_string)?$alert_string:null,
				'seted' => $seted,
				'ig_list' => $ig_list,
		]);
		return $this->fetch();
	}
	
	public function gettable() {
		return model('term')->getCurrentDays();
	}
	
	public function mutex() {
		$this->assign([
				
				
		]);
		return $this->fetch();
	}
	
	public function mutexlst() {
		$term = model('term');
		$table = model('table');
		$item = $term->where('is_cur',1)->find();
		$list = $table->getIg($item->code);
		$this->assign([
				'list' => $list,
				'term' => $term,
		]);
		return $this->fetch();
	}
	
	public function working() {
		$request = Request::instance();
		if($request->isAjax()) {
			$term = model('term');
			$table = model('table');
			$item = $term->where('is_cur',1)->find();
			if(!isset($item)) {
				return $alert_string = (new View())->assign([
						'type' => 'danger',
						'close' => false,
						'title' => '错误！',
						'body' => '未设置学期！',
						'href' => url('index/term/index'),
						'href_text' => '立即设置',
				])->fetch('public/alert');
			}
			if($table->getStatus($item->code)) {
				return $alert_string = (new View())->assign([
					'type' => 'danger',
					'close' => false,
					'title' => '错误！',
					'body' => '该学期已安排课表！',
					'href' => url('index/table/index'),
					'href_text' => '查看课表',
				])->fetch('public/alert');
			}
			$this->assign([
				'res_key' => rand(),
			]);
			return $this->fetch();
		} else {
			$this->error();
		}
	}
	
	public function background() {
		$request = Request::instance();
		if($request->isAjax()) {
			$res_key = $request->param('key');
			if(!empty($res_key)) {
				$table = model('table');
				$term = model('term');
				$item = $term->where('is_cur',1)->find();
				if(!isset($item)) {
					return getAjaxResp("未设置学期，无法排课！");
				}
				try {
					$msg = $table->doWorkList($item->code);
					return json([
							'success' => true,
							'msg' => $msg,
					]);
					return getAjaxResp("success", true);
				} catch (Exception $e) {
					return getAjaxResp("未知错误，请稍候重试！");
				}
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function item() {
		$request = Request::instance();
		if($request->isAjax()) {
			$week = $request->param('week');
			if(!empty($week)) {			
				$table = model('table');
				$rest = model('rest');
				$term = Term::getCurTerm();
				$list = null;
				$weeks = ['周一','周二','周三','周四','周五','周六','周日',];
				$ids = ['wk1','wk2','wk3','wk4','wk5','wk6','wk7',];
				$act_wk = date('w', time());
				if($act_wk == 0) {
					$act_wk = 7;
				}
				$act_wk--;
				$list = $table->getItem($term['code'], $week);
				$rest_list = $rest->getItem($term['code']);
				$this->assign([
						'term' => $term,
						'list' => $list,
						'weeks' => $weeks,
						'ids' => $ids,
						'act_wk' => $act_wk,
						'cur_week' => $week,
						'rest_list' => $rest_list,
				]);
				return $this->fetch('item2');
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function move() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			if(!empty($_id)) {
				$table = model('table');
				$machine = model('machine');
				$item = $table->getSingleItem($_id);
				$places = [];
				$arr = explode(',', $item['place']);
				foreach ($arr as $it) {
					array_push($places, $machine->getMachineName($it));
				}
				$this->assign([
						'_id' => $_id,
						'item' => $item,
						'places' => $places,
						'week_rep' => ['','周一','周二','周三','周四','周五','周六','周日'],
				]);
				return $this->fetch('move2');
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function move2() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			if(!empty($_id)) {
				$table = model('table');
				$machine = model('machine');
				$m_course = model('course');
				$term = Term::getCurTerm();
				
				$item = $table->getSingleItem($_id);
				$course = $m_course->getDetails($item['term'].$item['course']);
				
				$places = [];
				$arr = explode(',', $item['place']);
				foreach ($arr as $it) {
					array_push($places, $machine->getMachineName($it));
				}
				$differ = null;
				$d_term = date_create($term['start']);
				$d_now = date_create();
				if(date_timestamp_get($d_now) >= date_timestamp_get($d_term)) {
					$differ = floor(date_diff($d_term, $d_now)->format('%a') / 7) + 1;
				}
				$this->assign([
						'term' => $term,
						'_id' => $_id,
						'item' => $item,
						'course' => $course,
						'places' => $places,
						'week_rep' => ['','周一','周二','周三','周四','周五','周六','周日'],
						'now_week' => $differ,
				]);
				return $this->fetch('move2');
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function selector() {
		$request = Request::instance();
		if($request->isAjax()) {
			$week = $request->param('week');
			$_id = $request->param('id');
			if(!empty($week) && !empty($_id)) {
				$table = model('table');
				$machine = model('machine');
	
				$term = Term::getCurTerm();
				$list = null;
				$item = $table->getSingleItem($_id);
				$places = [];
				$arr = explode(',', $item['place']);
				foreach ($arr as $it) {
					array_push($places, $machine->getMachineName($it));
				}
				$mac_list = $machine->getItemList();
				$mac_idx_arr = [];
				foreach($mac_list as $mi) {
					$mac_idx_arr[$mi['name']] = $mi;
				}
				$weeks = ['周一','周二','周三','周四','周五','周六','周日',];
				$ids = ['wk1','wk2','wk3','wk4','wk5','wk6','wk7',];
				$act_wk = date('w', time());
				if($act_wk == 0) {
					$act_wk = 7;
				}
				$act_wk--;
				$list = $table->getItem($term['code'], $week);
				
				$differ = null;
				$d_term = date_create($term['start']);
				$d_now = date_create();
				if(date_timestamp_get($d_now) >= date_timestamp_get($d_term)) {
					$differ = floor(date_diff($d_term, $d_now)->format('%a') / 7) + 1;
				}
				
				$this->assign([
						'term' => $term,
						'list' => $list,
						'weeks' => $weeks,
						'ids' => $ids,
						'act_wk' => $act_wk,
						'_id' => $_id,
						'item' => $item,
						'places' => $places,
						'week_rep' => ['','周一','周二','周三','周四','周五','周六','周日'],
						'cur_week' => $week,
						'now_week' => $differ,
						'now_day' => (date('w', time())+6)%7,
						'mac_list' => $mac_idx_arr,
				]);
				return $this->fetch();
			} else {
				return getAjaxResp("param error");
			}
		} else {
			$this->error();
		}
	}
	
	public function domove() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id');
			$_week = $request->param('week');
			$_day = $request->param('day');
			$_section = $request->param('section');
			$_mac = $request->param('mac');
			$term = Term::getCurTerm();
			if(isset($_id) && isset($_week) && isset($_day) && isset($_section) && isset($_mac)) {
				try {
					$machine = new Machine();
					$_mac_ids = $machine->getMachineIds($_mac);
					$table = model('table');
					$item = $table->modify($_id, $_week, $_day, $_section, $_mac_ids);
					return json([
							'success' => true,
							'msg' => '课程'.$item['course'].'成功更改为第'.$item['week'].'周周'.$item['day'].'的'.$item['section'].'-'.($item['section'] + 1).'节在'.$_mac.'机房',
					]);
				} catch (MyException $e) {
					return getAjaxResp($e->getData('msg'));
				}
			}
			return getAjaxResp("未设置学期！");
		} else {
			$this->error();
		}
	}
}