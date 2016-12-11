<?php
namespace app\index\controller;

use think\Controller;

class Error extends BaseController
{
    public function index()
    {
    	$this->error();
    }
}
