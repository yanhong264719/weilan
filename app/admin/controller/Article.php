<?php
// +----------------------------------------------------------------------
// | Yan-ho [ WE ONLY DO WHAT IS NECESSARY ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.yanho.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yanho < 812580240@qq.com >
// +----------------------------------------------------------------------


namespace app\admin\controller;

use \think\Cache;
use \think\Controller;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
use app\admin\controller\Permissions;
use app\admin\model\Article as articleModel;
use app\admin\model\ArticleCate as cateModel;
use app\admin\model\ArticleGenerate as generateModel;
class Article extends Permissions
{   
    public function index()

    {    
        $cateModel = new cateModel();
        $session_a=input('session.admin/d');
        $model = new articleModel();
        $post = $this->request->param();
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['title'] = ['like', '%' . $post['keywords'] . '%'];
        }
        if (isset($post['article_cate_id']) and $post['article_cate_id'] > 0) {
            $where['article_cate_id'] = $post['article_cate_id'];
        }

        if (isset($post['admin_id']) and $post['admin_id'] > 0) {
            $where['admin_id'] = $post['admin_id'];
        }
        
        if (isset($post['status']) and ($post['status'] == 1 or $post['status'] === '0')) {
            $where['status'] = $post['status'];
        }

        if (isset($post['is_top']) and ($post['is_top'] == 1 or $post['is_top'] === '0')) {
            $where['is_top'] = $post['is_top'];
        }
 
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
        if ($session_a==1) {
           $articles = empty($where) ? $model->order('create_time desc')->paginate(10) : $model->where($where)->order('create_time desc')->paginate(10,false,['query'=>$this->request->param()]);
        }else{
            $articles = empty($where) ? $model->order('create_time desc')->where('admin_id',$session_a)->paginate(10) : $model->where($where)->order('create_time desc')->paginate(10,false,['query'=>$this->request->param()]);
        }
       
     
        //$articles = $article->toArray();
        //添加最后修改人的name
        foreach ($articles as $key => $value) {
            $articles[$key]['edit_admin'] = Db::name('admin')->where('id',$value['edit_admin_id'])->value('nickname');
          $id=$value['article_cate_id'];
          $articles[$key]['article_cate_id']=$cateModel->education($id);
        }
        $this->assign('articles',$articles);
        $info['cate'] = Db::name('article_cate')->select();
        $info['admin'] = Db::name('admin')->select();
        $this->assign('info',$info);
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
        return $this->fetch();
    }


    public function publish()
    {
      //获取菜单id
      $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
      $model = new articleModel();
        $cateModel = new cateModel();
    //是正常添加操作
    if($id > 0) {
        //是修改操作
        if($this->request->isPost()) {
          //是提交操作
          $post = $this->request->post();
          //验证  唯一规则： 表名，字段名，排除主键值，主键名
              $validate = new \think\Validate([
                 ['student_tel', 'mobile', '学生电话号码有误'],
                  ['home_tel', 'mobile', '家长电话号码有误'],
                  ['article_cate_id', 'require', '请选择专业/学历'],
              ]);
              //验证部分数据合法性
              if (!$validate->check($post)) {
                  $this->error('提交失败：' . $validate->getError());
              }
              //验证菜单是否存在
              $article = $model->where('id',$id)->find();
              if(empty($article)) {
                return $this->error('id不正确');
              }
                //设置修改人
                $post['edit_admin_id'] = Session::get('admin');
              if(false == $model->allowField(true)->save($post,['id'=>$id])) {
                return $this->error('修改失败');
              } else {
                    addlog($model->id);//写入日志
                return $this->success('修改成功','admin/article/index');
              }

        } else {
          //非提交操作
          $article = $model->where('id',$id)->find();
          if ($article['status']==1||$article['status']==2) {
           return $this->error('数据已经分配,不能修改');
          }
          $cates = $cateModel->select();
          $cates_all = $cateModel->catelist($cates);
          $this->assign('cates',$cates_all);
          $articlecate_id=$article['article_cate_id'];
          //查询文章分类名称
          $articlecateer_pid=$cateModel->field('id,name,pid')->where('id',$articlecate_id)->find();
          $catedata['id']=$articlecate_id;
          $catedata['san'] =$articlecateer_pid['name'];
          $cateer_pid_pid = $articlecateer_pid['pid'];
          $articlecatesan_pid=$cateModel->field('id,name,pid')->where('id',$cateer_pid_pid)->find();
          $catedata['er'] =$articlecatesan_pid['name'];
          $catesan_pid_pid = $articlecatesan_pid['pid'];
          $articlecatesi_pid=$cateModel->field('id,name,pid')->where('id',$catesan_pid_pid)->find();
          $catedata['yi'] =$articlecatesi_pid['name'];
          $catesi_pid_pid = $articlecatesi_pid['pid'];
          if(!empty($article)) {
            $this->assign('article',$article);
             $session_a=input('session.admin/d');
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
        $this->assign('catedata',$catedata);
        $province = db('article_cate')->where('pid', 0)->select();
          $this->assign(['province' => $province]);
            return $this->fetch();
          } else {
            return $this->error('id不正确');
          }
        }
      } else {
        //是新增操作
        if($this->request->isPost()) {
          //是提交操作
          $post = $this->request->post();
          //验证  唯一规则： 表名，字段名，排除主键值，主键名
              $validate = new \think\Validate([
                  ['student_tel', 'mobile', '学生电话号码有误'],
                  ['home_tel', 'mobile', '家长电话号码有误'],
                  ['article_cate_id', 'require', '请选择专业/学历'],
                  ['province', 'require', '省份不能为空'],
                  ['city', 'require', '城市不能为空'],
                  ['area', 'require', '地区不能为空'],
        
              ]);
              //验证部分数据合法性
              if (!$validate->check($post)) {
                  $this->error('提交失败：' . $validate->getError());
              }
                //设置创建人
                $post['admin_id'] = Session::get('admin');
                //设置修改人
                $student_tel=$post['student_tel'];
                $home_tel=$post['home_tel'];
                $checktel=Db::name('article')->where('student_tel',$student_tel)->whereOr('home_tel',$home_tel)->find();
                if ($checktel) {
                  return $this->error('电话号码已经存在');
                }
              if(false == $model->allowField(true)->save($post)) {
                return $this->error('添加失败');
              } else {
                    addlog($model->id);//写入日志
                return $this->success('添加成功','admin/article/index');
              }
        } else {
          //非提交操作
          $cate = $cateModel->select();
          $cates = $cateModel->catelist($cate);
          $session_a=input('session.admin/d');
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
          $this->assign('cates',$cates);
           $province = db('article_cate')->where('pid', 0)->select();
          $this->assign(['province' => $province]);
          return $this->fetch();
        }
      }
      
    }


    public function delete()
    {
      if($this->request->isAjax()) {
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $article=Db::name('article')->where('id',$id)->find();
        if ($article['status']==1||$article['status']==2) {
          return $this->error('数据已经分配,不能删除');
        }
            if(false == Db::name('article')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                addlog($id);//写入日志
                return $this->success('删除成功','admin/article/index');
            }
      }
    }


    public function is_top()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('article')->where('id',$post['id'])->update(['is_top'=>$post['is_top']])) {
                return $this->error('设置失败');
            } else {
                addlog($post['id']);//写入日志
                return $this->success('设置成功','admin/article/index');
            }
        }
    }


    public function status()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('article')->where('id',$post['id'])->update(['status'=>$post['status']])) {
                return $this->error('设置失败');
            } else {
                addlog($post['id']);//写入日志
                return $this->success('设置成功','admin/article/index');
            }
        }
    }
    public function design()
    {
       $cateModel = new cateModel();
       //ajax请求数据方法
        // $post = $this->request->param();
        // if (isset($post['id']) and $post['id'] > 0) {
        //     $where['pid'] = $post['id'];
        //     $data['data']=$cateModel->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);
        //     $data['code']=1;
        //     $data['msg']='成功获取数据';
        //     $data['url']=input('server.SERVER_NAME').url();
        //     $data['wait']=3;
        //     return $data;
        // };
        $post = $this->request->param();
        if (isset($post['area']) and !empty($post['keywords'])) {
            $where['title'] = ['like', '%' . $post['keywords'] . '%'];
        }
       //获取文章总数
       $article_number=Db::name("article")->select();
       $article_number = count($article_number);
       $article_cate=Db::name("article_cate")->where('pid',0)->select(); //获取所有以及分类
  
       $article_cates=array();                   
       $cateArr = array();
       foreach($article_cate as $key=>$val)
       {    
            $numbb=0;
            $erCate = Db::name("article_cate")->where('pid',$val['id'])->select();
            if($erCate)
            {
                foreach($erCate as $k=>$v)
                {
                    $sanCate = Db::name("article_cate")->where('pid',$v['id'])->select();
                    $numba=0;
                    foreach ($sanCate as $key => $value) {
                      $numbs=Db::name("article")->where('article_cate_id',$value['id'])->select();
                      $count = count($numbs);

                      $numbsa=Db::name("article")->where('article_cate_id',$value['id'])->where('qz','elt',3)->where('is_top',0)->select();//查询还可以使用的文章
                      $countsa = count($numbsa);

                      $sanCate[$key]['count']=$countsa;
                      $count=$count;
                      $numba+=$count;
                    }
                    $numbs1=Db::name("article")->where('article_cate_id',$v['id'])->select();
                    $count1 = count($numbs1);
                    $numba=$numba+$count1;
                    $erCate[$k] = $v;
                    $erCate[$k]['sanji'] = $sanCate;
                    $erCate[$k]['count'] = $numba;
                    $numbb+=$numba;
                }
            }
            $cateArr[$key] = $val; 
            $cateArr[$key]['erji'] = $erCate;
            $numbs2=Db::name("article")->where('article_cate_id',$val['id'])->select();
            $count2 = count($numbs2);
            $val['counts'] = $numbb+$count2;
            array_push($article_cates,$val);
       }
       // var_dump($cateArr);
       $this -> assign('cateArr',$cateArr);
       $this -> assign('article_number',$article_number);
       // var_dump($cateArr);
       //$id=$article_cate[0]['id'];
       $this->assign('article_cate',$article_cates);
     
       // $arr=array();
       // foreach ($article_cate as $value){
       //  $arp=array();
       //  $id=$value['id'];
       //  $cates_twos=Db::name('article_cate')->where('pid',$id)->field('id,name')->select();
       //  foreach ($cates_twos as $value) {
       //      array_push($arp,$value); 
       //  }
        
       //  array_push($arr,$arp);
       
       // }
       // var_dump($arr);
       // $this->assign('cates_two',$arr);
       
        // $cates_two = empty($where) ? $cateModel->where('pid',$id)->order('create_time desc')->paginate(20): $cateModel->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);
        // $this->assign('cates_two',$cates_two);
        $session_a=input('session.admin/d');
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
       return $this->fetch();
    }
    public function generate()
    {   

        $model=new articleModel();
        $title= $this->request->param('title');
        $id=[];

        $pid = input('post.pid/a');
         if ($pid) {
            $contents='';
            $keywords='';
            $description='';
            $thumb='';
        foreach ($pid as $key => $value) {
          $val=(int)$value;
          $content=Db::name('article')->field('id,content,thumb,tag,description')->where('article_cate_id',$val)->where('qz','elt',3)->where('is_top',0)->order('rand()')->limit(1)->select();
          if ($content) {
          $a=$content[0]['content'];
          $keywords=$content[0]['tag'];
          $description=$content[0]['description'];
          array_push($id,$content[0]['id']);
          $b=geturl($content[0]['thumb']);
          $d=$this->request->root(true);
          $thumb=$b;
          $thumbs=str_replace('\\','/',$thumb);//斜线转换
          $b='<img src="'.$d.$thumbs.'">';
          $c=$a.$b;
          $contents.=$c;
          }
          
        }
        $this->assign('title',$title);
        $this->assign('contents',$contents);
        $this->assign('keywords',$keywords);
        $this->assign('description',$description);
        $this->assign('thumb',$thumb);
        $this->assign('pid',$id);
        }else{
            $this->assign('pid',$id);
        }
         
        //$pid=$post['pid'];
        //var_dump($pid);
        // foreach ($pid as $value) {
        //     var_dump($value);
        // }
         $session_a=input('session.admin/d');
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
       return $this->fetch();
        
    }
    //保存生成的数据
    public function generatedata(){
      //是新增操作
                $model= new generateModel();
                $models= new articleModel();
            
                //是提交操作
                 $post = $this->request->post();
                //  $article_cate='';
                // foreach ($post['pid'] as $key => $value) {
                //     $article_cate=$article_cate.'-'.$value;
                // }
                // $post['article_cate']=$article_cate;
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '标题不能为空'],
                    ['content', 'require', '文章内容不能为空'],
                    ['keywords', 'require', '关键字不能为空'],
                    ['description', 'require', '描述不能为空'],
                    ['thumb', 'require', '图片地址不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //设置创建人
                $post['admin_id'] = Session::get('admin');
                //设置修改人
                if(false == $model->allowField(true)->save($post)) {
                    return $this->error('添加失败');
                } else {
                    //处理qz问题
                foreach ($post['pid'] as $key => $value) {
                    $id=(int)$value;
                    $data=[];
                    $time=time();
                    $qz=1;
                    $qzs=Db::name('article')->where('id',$id)->find();
                    $qz+=$qzs['qz'];

                    if (false ==$models->where('id',$id)->update(['qz' =>$qz,'update_time'=>$time])) {
                       array_push($data,'False'); 
                    }else{
                        array_push($data,'True');
                    };
                }
                if (in_array('False',$data)) {
                return $this->success('保存成功','admin/publish/index','权重修改失败');
                }else{
                return $this->success('保存成功','admin/publish/index','权重修改成功');
                }
                     
                } 
    }
    
    //汉字处理成拼音
    public function hanzi($data)
    { 
        $Pinyin = new \Org\Util\ChinesePinyin(); 
        // echo '带声调的汉语拼音'; 
        // $Pinyin->TransformWithTone($data); 
        // echo '<br/>'; 
        // echo '无声调的汉语拼音'; 
        return $Pinyin->TransformWithoutTone($data); 
        // echo '<br/>'; 
        // echo '首字母只包括汉字BuHanPinYin'; 
        // echo $Pinyin->TransformUcwordsOnlyChar("首字母只包括汉字BuHanPinYin"); 
        // echo '<br/>'; 
        // echo '首字母和其他字符如B区32号'; 
        // echo $Pinyin->TransformUcwords("首字母和其他字符如B区32号"); 
    }
    //原创发布
    public function profabu(){

        $id=input('post.id/d');
        $pid=array();
        if ($id) {
        array_push($pid,$id);
        $articleinfo=Db::name('article')->where('id',$id)->find();
        $thumb=geturl($articleinfo['thumb']);
        $a=$articleinfo['content'];
        //图片处理
          $d=$this->request->root(true);
          $thumbs=str_replace('\\','/',$thumb);//斜线转换
          $b='<img src="'.$d.$thumbs.'">';
          $c=$a.$b;
        $this->assign('title',$articleinfo['title']);
        $this->assign('contents',$c);
        $this->assign('keywords',$articleinfo['tag']);
        $this->assign('description',$articleinfo['description']);
        $this->assign('thumb',$thumb);
        $this->assign('pid',$pid);
        }
         $session_a=input('session.admin/d');
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
       return $this->fetch('generate');
    }
    public function shouluinfo()
    {   
        
        $session_a=input('session.admin/d');
        $model = new generateModel();
        $post = $this->request->param();
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['title'] = ['like', '%' . $post['keywords'] . '%'];
        }

        if (isset($post['admin_id']) and $post['admin_id'] > 0) {
            $where['admin_id'] = $post['admin_id'];
        }
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
        if ($session_a==1) {
           $articles = empty($where) ? $model->order('create_time desc')->where('shoulu',1)->whereOr('shoulum',1)->paginate(20) : $model->where('shoulu|shoulum','eq',1)->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);
        }else{
      $articles = empty($where) ? $model->order('create_time desc')->where('shoulu|shoulum','eq',1)->where('admin_id','eq',$session_a)->order('create_time desc')->paginate(20) : $model->where('shoulu|shoulum','eq',1)->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);
        }

        //$articles = $article->toArray();
        //添加最后修改人的name
        foreach ($articles as $key => $value) {
            $articles[$key]['edit_admin'] = Db::name('admin')->where('id',$value['edit_admin_id'])->value('nickname');
        }
        $this->assign('articles',$articles);
        $info['cate'] = Db::name('article_cate')->select();
        $info['admin'] = Db::name('admin')->select();
        $this->assign('info',$info);
        $session_a=input('session.admin/d');
        $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
        return $this->fetch();
    }
    //查询数据
    public function numberstot($session_a){
      if ($session_a==1) {
           $numbers_a=Db::name('article')->select();
           $numbers['guanli']=count($numbers_a);
           $numbers_b=Db::name('article_generate')->select();
           $numbers['fabu']=count($numbers_b);
           $numbers_c=Db::name('article_generate')->where('shoulu',1)->select();
           $numbers_c1=count($numbers_c);
           $numbers_cm=Db::name('article_generate')->where('shoulum',1)->select();
           $numbers_c2=count($numbers_cm);
           $numbers['shoulu']=$numbers_c1+$numbers_c2;
        }else{
           $numbers_a=Db::name('article')->where('admin_id',$session_a)->select();
           $numbers['guanli']=count($numbers_a);
           $numbers_b=Db::name('article_generate')->where('admin_id',$session_a)->select();
           $numbers['fabu']=count($numbers_b);
           $numbers_c=Db::name('article_generate')->where('admin_id',$session_a)->where('shoulu',1)->select();
           $numbers_c1=count($numbers_c);
           $numbers_cm=Db::name('article_generate')->where('admin_id',$session_a)->where('shoulum',1)->select();
           $numbers_c2=count($numbers_cm);
           $numbers['shoulu']=$numbers_c1+$numbers_c2;
        }
        return $numbers;
    }
    /**
     * AJAX获取标题库
     * @return [type] [description]
     */
    public function ajaxTitle()
    {
        if(input('post.queryString'))
        {   
            $val = input('post.queryString');
            $map['value'] = array('like',$val.'%');
            $requrst = Db::name("title")->field('value')->where($map)->order('rand()')->limit(10)->select();
            // $value = $requrst['value'];
            // $arr=array();
            // $arr['data'] = $requrst;
            // var_dump($value);exit();
            return $requrst;//'<li onClick="fill(\''.$value.'\');">'.$value.'</li>';//$arr;
        }
    }
    // 三级联动
  public function area()
  {
  
    if(request()->isAjax()){
      
      $code = input('post.code');
      $lists = db('article_cate')->where('pid', $code)->select();
      
      return json(['code' => 1, 'data' => $lists, 'msg' => 'ok']);
    }
    
    
  }

}
