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
namespace app\common\model\contrab;
use app\admin\model\ArticleGenerate as generateModel;
use think\Db;
use \think\Cookie;
use \think\Session;
class Order
{
   
	//定时任务开始
    public function timestart(){
      $time=time();
      $article_generate=Db::name('article_generate')->where('timetask','<',$time)->where('is_task',2)->where('status',0)->select();
      foreach ($article_generate as $article) {
            $this->timedata($article);
        }
        return '1';
    }
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
                $session=$article['admin_id'];
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

}