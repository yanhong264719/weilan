<?php
namespace app\api\controller;

use \think\Cache;
use \think\Controller;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
use \think\Request;
use app\index\controller\Checksystems;
use app\admin\model\Recruit;
use app\admin\model\Article;
use app\admin\model\Recruitinfo;
use app\admin\model\Messages;
class Index extends Checksystems
{
    public function index()
    {   
    	return 11;

    }
}