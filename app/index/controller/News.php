<?php
namespace app\index\controller;

use think\Controller;
use app\admin\model\Article;
class News extends Controller
{
    public function news()
    {
       $model=new Article();
       $banner=$model->where('article_cate_id',1)->where('status',1)->where('is_top',1)->select();
       $info['right']=$model->where('article_cate_id',2)->where('status',1)->where('is_top',1)->select();
       $info['left']=$model->where('article_cate_id',3)->where('status',1)->where('is_top',1)->limit(2)->select();
       $info['bottom']=$model->where('article_cate_id',4)->where('status',1)->where('is_top',1)->limit(5)->select();
       $this->assign('banner',$banner);
       $this->assign('info',$info);

        return view();
    }
}
