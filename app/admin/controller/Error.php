<?php
namespace app\admin\controller;
use \think\Controller;
/**
 * 这是一个空控制器
 */
class Error extends Controller
{
	public function index(){
		return redirect('index/index');
	}
	public function _empty(){
		return redirect('index/index');
	}
}