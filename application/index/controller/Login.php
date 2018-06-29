<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\View;
use app\index\model\Auth;
use think\Model;
use function think\error;
use app\index\model\Term;

class Login extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::login()) {
        	if(Session::has('redir') && Session::get('redir')) {
        		$_alert = '请先登录！';
        		Session::delete('redir');
        	}
        	$this->assign([
        			'title' => '用户登录',
        			'alert' => isset($_alert) ? $_alert : null,
        	]);
            return $this->fetch();
        } else {
            $this->redirect('index/index/index');
        }
    }
    
    public function tour() {
    	if (!Auth::login()) {
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
    				'title' => '课表总览（游客）',
    				'cols' => 4,
    				'rows' => 6,
    				'week' => isset($differ)?$differ:null,
    				'alert' => isset($alert_string)?$alert_string:null,
    				'seted' => $seted,
    				'ig_list' => $ig_list,
    		]);
    		return $this->fetch();
    	} else {
    		$this->redirect('index/index/index');
    	}
    }
    
    public function pdf(Request $request) {
    	$week = $request->param('week');
    	if(!empty($week)) {
    		$term = Term::getCurTerm();
    		$pdf_file_path = ROOT_PATH.'public/pdf/';
    		$pdf_file_name = $term['code'].'_'.$week.'.pdf';
    		$wkhtmltopdf_exec_url;
    		if(IS_WIN) {
    			$wkhtmltopdf_exec_url = config('pdf.exec_win').' '.config('pdf.param');
    		} else {
    			$wkhtmltopdf_exec_url = config('pdf.exec_unix').' '.config('pdf.param');
    		}
    		$pdf_url = $request->domain().url('index/login/view').'?week='.$week;
    		shell_exec($wkhtmltopdf_exec_url.' '.$pdf_url." ".$pdf_file_path.$pdf_file_name);
    		$this->redirect($request->domain()."/pdf/".$pdf_file_name, 200);
    	} else {
    		return getAjaxResp("param error");
    	}
    }
    
    public function view(Request $request) {
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
    		$date_str = getDateByWeek($term, $week);
    		$this->assign([
    				'term' => $term,
    				'list' => $list,
    				'weeks' => $weeks,
    				'ids' => $ids,
    				'act_wk' => $act_wk,
    				'cur_week' => $week,
    				'week_date' => $date_str,
    		]);
    		return $this->fetch('public/pdf');
    	} else {
    		return getAjaxResp("param error");
    	}
    }
    
    public function item(Request $request) {
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
    			return $this->fetch('item');
    		} else {
    			return getAjaxResp("param error");
    		}
    	} else {
    		$this->error();
    		
    	}
    }
    
    public function details() {
    	$request = Request::instance();
    	if($request->isAjax()) {
    		$code = $request->param('code');
    		$m_course = model('course');
    		$course = $m_course->getDetails($code);
    		$this->assign([
    				'course' => $course,
    		]);
    		return $this->fetch('course/details');
    	} else {
    		$this->error();
    	}
    }
    
    public function verify(Request $request) {
    	if($request->isAjax()) {
    		$_user = $request->param('user');
    		$_pwd = $request->param('password');
    		$_code = $request->param('captcha');
	        if (!empty($_user) && !empty($_pwd) && !empty($_code)) {
	        	if(!captcha_check($_code)) {
	        		// code error
	        		return getAjaxResp("验证码错误！", false, -123);
	        	}
	        	$auth = new Auth();
	        	if($auth->authUser($_user, $_pwd, $request->ip(0, true))) {
	        		Auth::login($_user, $auth->getName($_user));
                    return json([
                        'success' => true,
                        'url' => url('index/index/index'),
                        'msg' => 'success',
                    ]);
	        	} else {
	        		return getAjaxResp("用户名或密码错误!");
	        	}
	        } else {
	            return getAjaxResp("请输入完整信息!", false);
	        }
    	} else {
    		$this->error();
    	}
    }
    
    public function modify(Request $request) {
    	if($request->isAjax()) {
    		$user = $request->param('user');
    		$old = $request->param('old');
    		$pwd = $request->param('pwd');
    		$pwd2 = $request->param('pwd2');
    		if(Auth::login()) {
    			// has login
    			$cur_user = Session::get('user');
    			if($cur_user == $user && !empty($pwd) && !empty($pwd2) && $pwd == $pwd2) {
    				// user ok
    				$auth = new Auth();
    				if($auth->modifyPwd($user, $old, $pwd)) {
    					Auth::logout();
    					return json([
    							'success' => true,
    							'redir' => true,
    							'msg' => '密码修改成功，请重新登录',
    							'url' => url('index/login/index'),
    					]);
    				} else {
    					return getAjaxResp('原密码错误，请重新输入！');
    				}
    			} else {
    				// error user
    				return getAjaxResp('参数非法，请重试！');
    			}
    		} else {
    			// require login
    			return json([
    					'success' => false,
    					'msg' => '登录信息已过期，请重新登录！',
    					'redir' => true,
    					'url' => url('index/login/index'),
    			]);
    		}
    	} else {
    		$this->error();
    	}
    }

    public function out() {
    	Auth::logout();
        $this->redirect('index/login/index');
    }
}

