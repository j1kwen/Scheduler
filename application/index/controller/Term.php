<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use function think\error;
use think\Exception;

class Term extends Controller {
	
	public function index() {
		$term = model('term');
		$list = $term->order([
				'year' => 'desc',
				'term' => 'desc',
		])->select();
		$this->assign([
				'title' => '学期管理',
				'isadd' => isset($isadd) ? $isadd : false,
				'add_name' => isset($add_name) ? $add_name : '',
				'list' => $list,
		]);
		return $this->fetch();
	}
	
	public function add() {
		$request = Request::instance();
		if($request->isAjax()) {
			$term = model('term');
			$year = $request->param('year');
			$term_ = $request->param('term');
			$date_ = $request->param('date');
			try {
				$term->data([
						'year' => $year,
						'term' => $term_,
						'start' => $date_,
						'code' => $year.'0'.$term_,
						'is_cur' => 0,
				]);
				$term->save();				
				$ret = json([
						"success" => true,
						"msg" => "success",
				]);
			} catch (Exception $e) {
				$ret = json([
						"success" => false,
						"msg" => $year." ". $term_ . " " . $date_,
				]);
			}
			return $ret;
		} else {
			$this->error();
		}
	}
	
	public function del() {

		$request = Request::instance();
		if($request->isAjax()) {
			$s_id = $request->param('id','');
			$term = model('term');
			try {
				$term->where('id',$s_id)
				->delete();
				
				// TODO: delete other info about this term
				
				$ret = json([
						"success" => true,
						"msg" => "success",
				]);
			} catch (Exception $e) {
				$ret = json([
						"success" => false,
						"msg" => "param error"
				]);
			}
			return $ret;
		} else {
			$this->error();
		}
	}
	
	public function cur() {
		$request = Request::instance();
		if($request->isAjax()) {
			$s_id = $request->param('id','');
			$term = model('term');
			try {
				$term->where('is_cur',1)
				->update([
						'is_cur' => 0,
				]);
				$term->where('id',$s_id)
					->update([
							'is_cur' => 1,
					]);
				$ret = json([
						"success" => true,
						"msg" => "success",
				]);
			} catch (Exception $e) {
				$ret = json([
						"success" => false,
						"msg" => "param error"
				]);
			}
			return $ret;
		} else {
			$this->error();
		}
	}
	
	public function modify() {
		$request = Request::instance();
		if($request->isAjax()) {
			$_id = $request->param('id', null);
			$_date = $request->param('date', null);
			$term = model('term');
			try {
				$term->where('id', $_id)
				->update([
						'start' => $_date,
				]);
				$ret = json([
						"success" => true,
						"msg" => "success",
				]);
			} catch (Exception $e) {
				$ret = json([
						"success" => false,
						"msg" => "param error"
				]);
			}
			return $ret;
		} else {
			$this->error();
		}
	}
}