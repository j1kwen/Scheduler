<?php

namespace app\index\model;

use think\Model;
use think\Session;

class Auth extends Model {
	
	protected $pk = 'user';
	protected $table = 'admin';
	
	protected function initialize() {
		parent::initialize();
	}
	
	public function authUser($user, $pwd, $ip='127.0.0.1') {
		$db_user = $this->where('user', $user)->find();
		if(empty($db_user) || ($db_user['passwd'] != $pwd)) {
			return false;
		}
		$this->where('user', $user)->update([
				'logintime' => time(),
				'loginip' => $ip,
		]);
		return true;
	}
	
	public function modifyPwd($user, $old, $pwd) {
		$db_user = $this->where('user', $user)->find();
		if(empty($db_user) || ($db_user['passwd'] != $old)) {
			return false;
		}
		$this->where('user', $user)->update([
				'passwd' => $pwd,
		]);
		return true;
	}
	
	public function getName($user) {
		$db_user = $this->where('user', $user)->find();
		if(empty($db_user)) {
			return null;
		}
		return $db_user['name'];
	}
	/**
	 * session登录用户或查询用户是否登录
	 * @param string $user
	 * @param string $name
	 * @return bool|void
	 */
	public static function login($user = null, $name = null) {
		if(empty($user)) {
			return Session::has('user', 'scheduler');
		}
		if(empty($name)) {
			$auth = new Auth();
			$name = $auth->getName($user);
		}
		session('user', $user, 'scheduler');
		session('name', $name, 'scheduler');
	}
	/**
	 * 注销
	 */
	public static function logout() {
		Session::clear('scheduler');
	}
}