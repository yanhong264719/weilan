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
class Index extends Checksystems
{
    public function index()
    {   
        //网站关键字
        $keywords=db('webconfig')->find();
        $this->assign('keywords',$keywords);
        return $this->fetch();
    }
   public function dataadd(){
    $model=new Recruit(); 
     $res['code']=1;
     $res['msg']='success';
     $page=$this->request->param('page');
     $city=$this->request->param('city');
     $keywords=$this->request->param('keywords');
     $where['title']= ['like', '%' . $keywords . '%'];
     //$where['m_username|mnickname'] = array('like', "%{$keyword}%", 'or');多字段查询
     if ($city!==''&&$keywords!=='') {
        $count=Db::name('recruit')->where('status',1)->where($where)->where('city',$city)->count();
        $articles=$model->where('status',1)->where($where)->where('city',$city)->limit($page.',6')->select();
       
     }else if($keywords!==''){
        $count=Db::name('recruit')->where('status',1)->where($where)->count();
        $articles=$model->where('status',1)->where($where)->limit($page.',6')->select();
     }else if($city!==''){
        $count=Db::name('recruit')->where('status',1)->where('city',$city)->count();
        $articles=$model->where('status',1)->where('city',$city)->limit($page.',6')->select();
     }else{
         $count=Db::name('recruit')->where('status',1)->count();
         $articles=$model->where('status',1)->order('create_time desc')->limit($page*6,6)->select();
     }
     foreach ($articles as $key => $vo) {
        $att=$articles[$key]['thumb'];
        $img=db('attachment')->where('id',$att)->value('filepath');
        $articles[$key]['thumb']= $img;
        //处理随机数
        $peoples=$articles[$key]['peoples'];
         $articles[$key]['num']=rand(0,$peoples);
         //标签处理
         $tag=$articles[$key]['tag'];
         $tag=explode("、",$tag);
         $articles[$key]['tag']=$tag;
     }
     $res['data']=$articles;
     $res['pages']=ceil($count/6);
     return $res;
   }
   //内容页面处理
   public function infos(){
        $model=new Recruit(); 
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $keywords=db('webconfig')->find();
        $this->assign('keywords',$keywords);
        $recruit=$model->where('status',1)->where('id',$id)->find();
        $this->assign('recruit',$recruit);
        $pictures=$recruit['pictures'];
        $info['pictures']=explode(",",$pictures);
        $tag=$recruit['tag'];
        $info['tag']=explode("、",$tag);
        $info['num']=rand(10,$recruit['peoples']);
        $data=$model->where('status',1)->where('is_top',1)->limit(5)->select();
        foreach ($data as $key => $vo){
         $tags=$data[$key]['tag'];
         $tags=explode("、",$tags);
         $data[$key]['tag']=$tags;
        }
       $this->assign('info',$info); 
       $this->assign('data',$data); 
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
//报名方法
public function baoming(){
     //网站关键字
        $model=new Recruit();
        $get = $this->request->param('id');
        $data=$model->where('id',$get)->find();
        $this->assign('data',$data);
        $keywords=db('webconfig')->find();
        $this->assign('keywords',$keywords);
        return $this->fetch();
}

 public function login()
    {
          $model=new Tel();
            if($this->request->isPost()) {
                //是登录操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['name', 'require|chsAlpha', '用户名不能为空|用户名格式只能是字母、汉字'],
                    ['mobile', 'require|max:11|/^1[3-8]{1}[0-9]{9}$/', '密码不能为空|电话必须11位|电话号码格式错误'],
                    ['email','require|email','邮箱不能为空|邮箱不正确'],
                    ['captcha','require|captcha','验证码不能为空|验证码不正确'],
                    
                ]);
                //验证部分数据合法性
                $post['ip']=$this->request->ip();
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                $mobile = $model->where('mobile',$post['mobile'])->find();
                if(!empty($mobile)) {
                    return $this->error('号码已经存在');
                }
                if (false == $model->allowField(true)->save($post)) {
                     return $this->error('提交失败');
                }else{
                    return $this->success('提交成功');
                }
                
            } else {
               return $this->error('数据出错,请从新提交');
            }
        
    }   










}