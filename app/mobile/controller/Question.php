<?php
namespace app\mobile\controller;

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
use app\mobile\model\Tel;
class Question extends Checksystems
{
    public function index()
    {   
        //网站关键字
        $model_a=new Messages();
        $keywords=db('webconfig')->find();
        $this->assign('keywords',$keywords);
        //接受id
        $model=new Recruit();
        $get = $this->request->param('id');
        $data=$model->where('id',$get)->find();
        //显示数据
        $messages=$model_a->where('recruit_id',$get)->where('is_look',1)->select();
        $this->assign('data',$data);
        $this->assign('messages',$messages);
        return $this->fetch();
    }
    public function input(){
        $model=new Recruit();
        $get = $this->request->param('id');
        $data=$model->where('id',$get)->find();
        $this->assign('data',$data);
        $keywords=db('webconfig')->find();
        $this->assign('keywords',$keywords);
        return $this->fetch();
    }
   










}