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
use app\admin\model\ArticleGenerate as generateModel;
class Publish extends Permissions
{
 //文章发布
     public function index()
    {    
        $session_a=input('session.admin/d');
        $model = new generateModel();
        $post = $this->request->param();
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['title'] = ['like', '%' . $post['keywords'] . '%'];
        }
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }

        if (isset($post['admin_id']) and $post['admin_id'] > 0) {
            $where['admin_id'] = $post['admin_id'];
        }
        
        // if (isset($post['status']) and ($post['status'] == 1 or $post['status'] === '0')) {
        //     $where['status'] = $post['status'];
        // }

        // if (isset($post['is_top']) and ($post['is_top'] == 1 or $post['is_top'] === '0')) {
        //     $where['is_top'] = $post['is_top'];
        // }
        
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
         if ($session_a==1) {
           $articles = empty($where) ? $model->order('create_time desc')->paginate(20) : $model->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);
        }else{
            $articles = empty($where) ? $model->order('create_time desc')->where('admin_id',$session_a)->paginate(20) : $model->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);
        }
     
        //$articles = $article->toArray();
        //添加最后修改人的name
          foreach ($articles as $key => $value) {
            $articles[$key]['admin_id'] = Db::name('admin')->where('id',$value['admin_id'])->value('nickname');

        }
        $this->assign('articles',$articles);
        // $info['cate'] = Db::name('article_cate')->select();
        $info['admin'] = Db::name('admin')->select();
        $this->assign('info',$info);
     //    $b=array();
     //     $cate_a=Db::name('publish_cate')->where('pid',0)->select();
     //     foreach ($cate_a as $key => $value) {
     //       $a=array();
     //       $a['code']=$cate_a[$key]['id'];
     //       $a['name']=$cate_a[$key]['name'];
     //       $cate_b=Db::name('publish_cate')->where('pid',$cate_a[$key]['id'])->select();
     //       foreach ($cate_b as $key => $value) {
     //         $c['childs']=['code'=>$cate_b[$key]['id'],'name'=>$cate_b[$key]['name']];
     //         array_push($a,$c);
     //         $cate_c=Db::name('publish_cate')->where('pid',$cate_b[$key]['id'])->select();
     //         foreach ($cate_c as $key => $value) {
     //           $a['childs']['childs']=['code'=>$cate_c[$key]['id'],'name'=>$cate_c[$key]['name']];
     //         }
     //       }
     //     array_push($b,$a);
     //     }
     // var_dump(json_encode($b));
     //    $val=['code'=>1,'name'=>'www.cvvcoo.com','childs'=>['code'=>10,'name'=>'http://www.cvvcoo.com','childs'=>[['code'=>1101,'name'=>'aa'],['code'=>'1102','name'=>'bb']]]];
        // $var=json_decode([{"code":"12","name":"www.cvvcoo.com","childs":[{"code":"1201","name":"http://www.cvvcoo.com/bt/api.php","childs":[{"code":"120119","name":"就业资讯"},{"code":"120120","name":"行业资讯"},{"code":"120121","name":"校园新闻"},{"code":"120122","name":"新生故事"}]}]}]);
         $numbers=$this->numberstot($session_a);
        $this->assign('numbers',$numbers);
        return $this->fetch();
    }
    public function fabu(){
        $model = new generateModel();
        $generate_id= input('post.content/d');
        if ($generate_id) {
           // $data=$model->where('id',$id)->find();
            $this->assign('generate_id',$generate_id);
        }
        return $this->fetch();
    }
    public function fabus(){
        $model = new generateModel();
       
        if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['province', 'require', '网址不能为空'],
                    ['city', 'require', '接口不能为空'],
                    ['area', 'require', '分类不能为空'],
                    ['generate_id', 'require', 'id不能为空'],
                ]);

                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //接受数据
                $province=$post['province'];
                $api_url=$post['city'];
                $cate=$post['area'];
                $generate_id=$post['generate_id'];
                //验证数据是否存在
                $article = $model->where('id',$generate_id)->find();
                if(empty($article)) {
                    return $this->error('id不正确');
                }else{
                $title=$article['title'];
                $content=$article['content'];
                $keywords=$article['keywords'];
                $description=$article['description'];
                $picture=$article['thumb'];
                $picture=str_replace('\\','/',$picture);//斜线转换
                $session=input('session.admin/d');
                $session=Db::name('admin')->where('id',$session)->value('name');
        
                //组装数据
                $data = array("title" => $title,
                              "content" => $content,
                              "picture" => $picture,
                              "cate" => $cate,
                              "description" => $description,
                              "keywords" => $keywords,
                              "admin" => $session,
                          );
            //     $data = http_build_query($data);
            //     $opts = array(
            //        'http'=>array(
            //          'method'=>"POST",
            //          'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
            //                    "Content-length:".strlen($data)."\r\n" .
            //                    "Cookie: admin=".$session."\r\n" .
            //                    "\r\n",
            //          'content' => $data,
            //        )
            //      );
    
            // //发布请求
            //  $cxContext = stream_context_create($opts);
            //  $sFile = file_get_contents($api_url, false, $cxContext);
            //  // if ($sFile['code']==1) {
            //  //   return $this->success($picture);
            //  // }else{
            //  //   return $this->error($picture);
            //  // }
            //  $str=trim($sFile,"\"");
            //   var_dump($str);
                $datas=$this->requests($api_url,$data);
				//var_dump($datas['data']);
               if ($datas['code']==200) {//执行成功写入数据库
                 $updates['urlpc']=$datas['data']['urlpc'];
                 $updates['urlm']=$datas['data']['urlm'];
                 $updates['status']=$datas['code'];
                 $updates['apiurl']=$api_url;
                 $updates['article_cate']=$cate;
                 $updates['is_task']=1;
                 $updates['timetask']=time();
                 //$updates['article_push']=$datas['push']?$datas['push']:2;
                 //$updates['createxml']=$datas['createxml']?$datas['createxml']:2;
                 //开始写入数据库
                  if(false == $model->allowField(true)->save($updates,['id'=>$generate_id])){
                         return $this->error('发布失败');
                  }else{
                     return $this->success('发布成功','admin/publish/index');
                  }
               }else{
                 $updates['status']=201;
                 $updates['apiurl']=$api_url;
                 //开始写入数据库
                  if(false == $model->allowField(true)->save($updates,['id'=>$generate_id])){
                         return $this->error('发布失败');
                  }else{
                    
                     return $this->error('发布失败','admin/publish/index');
                  }
               }
              
              }
            }
       
    }
    //定时发布
    public function timefabu(){
        $model = new generateModel();
        $generate_id= input('post.content/d');
        if ($generate_id) {
           // $data=$model->where('id',$id)->find();
            $this->assign('generate_id',$generate_id);
        }
        return $this->fetch();
    }
    //获取定时发布的数据
    public function timefabus(){
        $model = new generateModel();
       
        if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['province', 'require', '网址不能为空'],
                    ['city', 'require', '接口不能为空'],
                    ['area', 'require', '分类不能为空'],
                    ['generate_id', 'require', 'id不能为空'],
                    ['timeout', 'require', '时间不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //接受数据
                $province=$post['province'];//网站地址
                $datas['apiurl']=$post['city'];//api接口
                $datas['article_cate']=$post['area'];//文章分类
                $generate_id=$post['generate_id'];//文章id
                $timeout=$post['timeout'];//定时时间
                $timeout=strtotime($timeout);
                $datas['timetask']=$timeout;
                $datas['is_task']=2;
                $newtime=time();
                if($newtime>$timeout){
                return $this->error('定时出错');//判断时间是否大于现在时间
                };         
                //验证数据是否存在
                $article = $model->where('id',$generate_id)->find();
                if(empty($article)){
                return $this->error('id不正确');//判断文章是否存在
                };
                if ($article['status']==200) {
                 return $this->error('已经发布成功不能再定时');
                };
                //把定时任务存进数据库
                if(false == $model->allowField(true)->save($datas,['id'=>$generate_id])) {
                return $this->error('任务定时失败');
              } else {
                return $this->success('任务定时成功','admin/publish/index');
              }
    }
    }
    //定时任务开始
    public function timestart(){
      $time=time();
      $article_generate=Db::name('article_generate')->where('timetask','<',$time)->where('is_task',2)->select();
      foreach ($article_generate as $article) {
            $this->timedata($article);
        }
        return '1';
    }
    //请求数据
    public function requests($url,$data){
       //初始化
              $curl = curl_init();
              //图片上传
              $path='D:\wwwroot\wenku\wwwroot\public'.$data['picture'];
              if (class_exists('\CURLFile')) {
              curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
               $data['file'] =new \CURLFile(realpath($path));//>=5.5
               } else {
                 if (defined('CURLOPT_SAFE_UPLOAD')) {
                 curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
                 }
                 $data['file'] ='@' . realpath($path);//<=5.5
                }
               //设置抓取的url
              curl_setopt($curl, CURLOPT_URL,$url);
              //设置头文件的信息作为数据流输出
              curl_setopt($curl, CURLOPT_HEADER, 0);
              //设置获取的信息以文件流的形式返回，而不是直接输出。
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
              //设置post方式提交
              curl_setopt($curl, CURLOPT_POST, 1);
              //设置post数据
              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
             //执行命令
              $datas = curl_exec($curl);
              $datas=json_decode($datas,true);
              $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE); 
              //关闭URL请求
              curl_close($curl);
              //显示获得的数据
              $ok=array(
                  "code"=>$httpCode,
                  "data"=>$datas
              );
             return $ok;
    }
    //查收录
    public function baiducheck($url){
    $url='http://www.baidu.com/s?wd='.$url;
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $rs=curl_exec($curl);
    curl_close($curl);
    if(!strpos($rs,'没有找到该URL。您可以直接访问') && !strpos($rs,'很抱歉，没有找到与') ){
        return 1;
    } else {
        return 0;
    }
    
  }
  //生成文章删除
    public function delete()
    {
      if($this->request->isAjax()) {
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('article_generate')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                addlog($id);//写入日志
                return $this->success('删除成功','admin/publish/index');
            }
      }
    }
 //生成文章修改
    public function publishs(){
      $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
      $model = new generateModel();
      $article = $model->where('id',$id)->find();
      $this->assign('article',$article);
      return $this->fetch();
    }
 
    public function publishupdate()
    {
      //获取菜单id
      $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
      $model = new generateModel();
    //是正常添加操作
    if($id > 0) {
        //是修改操作
        if($this->request->isPost()) {
          //是提交操作
          $post = $this->request->post();
         //验证  唯一规则： 表名，字段名，排除主键值，主键名
              $validate = new \think\Validate([
                  ['title', 'require', '标题不能为空'],
                  ['keywords', 'require', '关键字不能为空'],
                  ['thumb', 'require', '请上传缩略图'],
                  ['description', 'require', '文章描述不能为空'],
                  ['content', 'require', '文章内容不能为空'],
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
                return $this->success('修改成功','admin/publish/index');
              }
        }
      }
      
    }
// $url='http://wenku.glvea.com';baidu_check($url)
// if(baidu_check($url ) == 1) {
//     echo '<a target="_blank" title="点击查看" rel="external nofollow" href="http://www.baidu.com/s?wd='.$url.'">百度已收录</a>';
// } else {
//     echo '<a style="color:red;" rel="external nofollow" title="点击提交，谢谢您！" target="_blank" href="http://zhanzhang.baidu.com/sitesubmit/index?sitename='.$url.'">百度未收录</a>';
// }
//
    public function timedata($article){
               $model = new generateModel();
                $id=$article['id'];
                $title=$article['title'];
                $content=$article['content'];
                $keywords=$article['keywords'];
                $description=$article['description'];
                $cate=$article['article_cate'];
                $api_url=$article['apiurl'];
                $picture=$article['thumb'];
                $picture=str_replace('\\','/',$picture);//斜线转换
                $session=input('session.admin/d');
                $session=Db::name('admin')->where('id',$session)->value('name');
        
                //组装数据
                $data = array("title" => $title,
                              "content" => $content,
                              "picture" => $picture,
                              "cate" => $cate,
                              "description" => $description,
                              "keywords" => $keywords,
                              "admin" => $session,
                          );
                $datas=$this->requests($api_url,$data);
              
               if ($datas['code']==200) {//执行成功写入数据库
                 $updates['urlpc']=$datas['data']['urlpc'];
                 $updates['urlm']=$datas['data']['urlm'];
                 $updates['status']=$datas['code'];
                 //开始写入数据库
                  $model->allowField(true)->save($updates,['id'=>$id]);
              
               }else{
                 $updates['status']=$datas['code'];
                  $updates['status']=201;
                 //开始写入数据库
                  $model->allowField(true)->save($updates,['id'=>$id]);
                   
               }
              
    }
    //数据统计
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
  

}