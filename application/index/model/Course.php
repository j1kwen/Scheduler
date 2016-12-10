<?php
namespace app\index\model;

use think\Model;

class Course extends Model {
	
	protected $pk = 'id';
	
	protected function initialize() {
		parent::initialize();
	}
}