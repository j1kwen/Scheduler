<?php
namespace app\index\controller;

use think\Controller;
use think\View;

class Index extends Controller
{
    public function index()
    {
    	$term = model('term');
    	$item = $term->where('is_cur',1)->find();
    	$rep_week = [
    			'日',
    			'一',
    			'二',
    			'三',
    			'四',
    			'五',
    			'六'
    	];
    	if(isset($item)) {
    		$d_term = date_create($item->start);
    		$d_now = date_create();
    		if(date_timestamp_get($d_now) >= date_timestamp_get($d_term)) {    			
    			$differ = floor(date_diff($d_term, $d_now)->format('%a') / 7) + 1;
    		}
    	}
    	$alert_string = (new View())->assign([
    			'type' => 'success',
    			'close' => true,
    			'title' => '登录成功！',
    			'body' => '已作为系统管理员登录',
    	])->fetch('Public/alert');
    	$this->assign([
				'title' => '首页',
    			'item' => $item,
    			'rep_week' => $rep_week,
    			'_day' => date('w', time()),
				'_week' => isset($differ)?$differ:null,
    			'alert_string' => $alert_string,
    	]);
    	return $this->fetch();
    }
}
