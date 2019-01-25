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
        $model = new generateModel();
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
        
        $articles = empty($where) ? $model->order('create_time desc')->paginate(8) : $model->where($where)->order('create_time desc')->paginate(8,false,['query'=>$this->request->param()]);
     
        //$articles = $article->toArray();
        //添加最后修改人的name
          foreach ($articles as $key => $value) {
            $articles[$key]['admin_id'] = Db::name('admin')->where('id',$value['admin_id'])->value('nickname');

        }
        $this->assign('articles',$articles);
        //$info['cate'] = Db::name('article_cate')->select();
        //$info['admin'] = Db::name('admin')->select();
        // $this->assign('info',$info);
        $b=array();
         $cate_a=Db::name('publish_cate')->where('pid',0)->select();
         foreach ($cate_a as $key => $value) {
           $a=array();
           $a['code']=$cate_a[$key]['id'];
           $a['name']=$cate_a[$key]['name'];
           $cate_b=Db::name('publish_cate')->where('pid',$cate_a[$key]['id'])->select();
           foreach ($cate_b as $key => $value) {
             $a['childs']=['code'=>$cate_b[$key]['id'],'name'=>$cate_b[$key]['name']];
             $cate_c=Db::name('publish_cate')->where('pid',$cate_b[$key]['id'])->select();
             foreach ($cate_c as $key => $value) {
               $a['childs']['childs']=['code'=>$cate_c[$key]['id'],'name'=>$cate_c[$key]['name']];
             }
           }
         array_push($b,$a);
         }
     var_dump(json_encode($b));
        $val=['code'=>1,'name'=>'www.cvvcoo.com','childs'=>['code'=>10,'name'=>'http://www.cvvcoo.com','childs'=>[['code'=>1101,'name'=>'aa'],['code'=>'1102','name'=>'bb']]]];
     

        // $var=json_decode([{"code":"12","name":"www.cvvcoo.com","childs":[{"code":"1201","name":"http://www.cvvcoo.com/bt/api.php","childs":[{"code":"120119","name":"就业资讯"},{"code":"120120","name":"行业资讯"},{"code":"120121","name":"校园新闻"},{"code":"120122","name":"新生故事"}]}]}]);

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
                }
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
                          );
                $data = http_build_query($data);
                $opts = array(
                   'http'=>array(
                     'method'=>"POST",
                     'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
                               "Content-length:".strlen($data)."\r\n" .
                               "Cookie: admin=".$session."\r\n" .
                               "\r\n",
                     'content' => $data,
                   )
                 );
    
            //发布请求
             $cxContext = stream_context_create($opts);
             $sFile = file_get_contents($api_url, false, $cxContext);  
              return $this->success($picture);
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
                $province=$post['province'];
                $api_url=$post['city'];
                $cate=$post['area'];
                $generate_id=$post['generate_id'];
                $timeout=$post['timeout'];
                $timeout=time($timeout);
                $dates=date('Y-m-d H:i:s',$timeout);
                echo $timeout;
                echo $dates;
                exit;
                //验证数据是否存在
                $article = $model->where('id',$generate_id)->find();
                if(empty($article)) {
                    return $this->error('id不正确');
                }
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
                          );
                $data = http_build_query($data);
                $opts = array(
                   'http'=>array(
                     'method'=>"POST",
                     'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
                               "Content-length:".strlen($data)."\r\n" .
                               "Cookie: admin=".$session."\r\n" .
                               "\r\n",
                     'content' => $data,
                   )
                 );
    
            //发布请求
             $cxContext = stream_context_create($opts);
             $sFile = file_get_contents($api_url, false, $cxContext);  
              return $this->success($picture);
            }
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
// $url='http://wenku.glvea.com';baidu_check($url)
// if(baidu_check($url ) == 1) {
//     echo '<a target="_blank" title="点击查看" rel="external nofollow" href="http://www.baidu.com/s?wd='.$url.'">百度已收录</a>';
// } else {
//     echo '<a style="color:red;" rel="external nofollow" title="点击提交，谢谢您！" target="_blank" href="http://zhanzhang.baidu.com/sitesubmit/index?sitename='.$url.'">百度未收录</a>';
// }

}