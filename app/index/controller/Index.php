<?php
namespace app\index\controller;

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
    	// $model=new Recruit();
    	// $model1=new Article();
     //    $recruit['long'] = $model->where('is_top',1)->where('status',1)->where('recruitinfo_id',4)->limit(4)->select();
     //    $recruit['time'] = $model->where('is_top',1)->where('status',1)->where('recruitinfo_id',5)->limit(5)->select();
     //    $recruit['chubei'] = $model->where('is_top',1)->where('status',1)->where('recruitinfo_id',1)->limit(4)->select();
     //    $this->assign('recruit',$recruit);
     //    $info['cate'] = Db::name('recruitinfo')->select();
     //    $info['ment'] = Db::name('attachment')->select();
     //    $info['article'] = $model1->where('is_top',1)->where('status',1)->where('article_cate_id',2)->limit(6)->select();
     //    $info['articles'] = $model1->where('is_top',1)->where('status',1)->where('article_cate_id',1)->order('create_time desc')->limit(10)->select();
     //    $info['last'] = $model1->where('is_top',1)->where('status',1)->where('article_cate_id',4)->find();
     //    $info['last_second'] = $model1->where('is_top',1)->where('status',1)->where('article_cate_id',3)->find();
     //    //网站关键字
     //    $keywords=db('webconfig')->find();
     //    $this->assign('info',$info);
     //    $this->assign('keywords',$keywords);
        return $this->fetch();
    }
    public function infos(){
       $model_a=new Messages();
    	//颜值参数
            if($this->request->isGet()) {
                //是提交操作
                $get = $this->request->param();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['id', 'require|number', '找不到该页面'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($get)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                }
        //查值   
                $model=new Recruit();
                $recruits= $model->where('id',$get['id'])->find();
                //右边和下边推荐
                $right= $model->where('is_top',1)->order('create_time desc')->limit(0,4)->select();
                $bottom= $model->where('is_top',1)->where('status',1)->where('recruitinfo_id',4)->limit(5)->select();
                if(!empty($recruits)) {
                    $messages=$model_a->where('recruit_id',$get['id'])->where('is_look',1)->paginate(3);
                    $page =$messages->render();
                    $info=Db::name('reply')->select();
                   //var_dump($messages);
                    $total=json_decode(json_encode($messages), true);
                    $info['total']=$total['total'];
                    $this->assign('recruits',$recruits);
                    $this->assign('page',$page);
                    $this->assign('info',$info);
                    $pictures=$recruits['pictures'];
                    $pictures=explode(",",$pictures);
                    $tot=count($pictures);
                    $this->assign('pictures',$pictures);
                    $this->assign('tot',$tot);
                    $tag=$recruits['tag'];
                    $tag=explode(",",$tag);
                    $this->assign('tag',$tag);
                    $this->assign('messages',$messages);
                    $this->assign('right',$right);
                    $this->assign('bottom',$bottom);
                } else {
                    return $this->error('找不到该页面');
                }


        //渲染页面
            return $this->fetch();
}
//map方法
public function map(){
	$get = $this->request->param();
	$model=new Recruit();
    $recruits= $model->where('id',$get['id'])->find();
    if(!empty($recruits)) {
        $this->assign('recruits',$recruits);
    } else {
        return $this->error('找不到该页面');
    }
	return $this->fetch();
}
//文章处理方法

public function articleinfo(){
	$get = $this->request->param();
	$model=new Article();
    $models=new Recruit();
    $articleinfo= $model->where('id',$get['id'])->find();
    $bottom= $model->where('status',1)->where('is_top',1)->where('article_cate_id',1)->limit(4)->select();
    $right_top= $model->where('status',1)->where('is_top',1)->where('article_cate_id',2)->limit(6)->select();
    $right_last= $model->where('status',1)->where('is_top',1)->where('article_cate_id',3)->limit(6)->select();
    $last= $models->where('status',1)->where('is_top',1)->where('recruitinfo_id',4)->limit(0,5)->select();
    $right_bottom= $model->where('status',1)->where('is_top',1)->where('article_cate_id',4)->find();
    $admin= db('admin')->where('id',$articleinfo['admin_id'])->value('nickname');
    if(!empty($articleinfo)) {
        $this->assign('articleinfo',$articleinfo);
        $this->assign('admin',$admin);
        $this->assign('bottom',$bottom);
        $this->assign('right_top',$right_top);
        $this->assign('right_bottom',$right_bottom);
        $this->assign('right_last',$right_last);
        $this->assign('last',$last);
    } else {
        return $this->error('找不到该页面');
    }
	return $this->fetch();
}
//留言处理
 public function mess(Request $request)
    {
        $model = new Messages();
        //是新增操作
        $post=$request->param();
        $post['message']=$request->param('message','','strip_tags,strtolower');
        $post['ip'] = $this->request->ip();
        $data=$model->where('ip',$post['ip'])->order('create_time desc')->find();
        if ($data) {
            if ($data['is_look']==0) {
                #限制提交次数
            return $this->error('数据审核中,请在审核通过后提交数据');
            }
            
        }
        if($this->request->isPost()) {
            //是提交操作
            
            //验证  唯一规则： 表名，字段名，排除主键值，主键名
            $validate = new \think\Validate([
                ['message', 'require|length:5,150', '留言不能为空|请输入10-100个字的建议'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($post)) {
                $this->error('提交失败：' . $validate->getError());
            }
            //设置创建人
            $post['ip'] = $this->request->ip();
            $post['reply_id'] =0;
            if(false == $model->allowField(true)->save($post)) {
                return $this->error("留言失败");
            } else {
                
                return $this->success("留言成功");
            }
        } else {
            //非提交操作
          echo 2; 
        }
    }
}