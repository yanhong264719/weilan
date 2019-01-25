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

use \think\Db;
use \think\Cookie;
use app\admin\controller\Permissions;
class Main extends Permissions
{
    public function index()
    {   

        //tplay版本号
        $info['tplay'] = TPLAY_VERSION;
        //tp版本号
        $info['tp'] = THINK_VERSION;
        //php版本
        $info['php'] = PHP_VERSION;
        //操作系统
        $info['win'] = PHP_OS;
        //最大上传限制
        $info['upload_size'] = ini_get('upload_max_filesize');
        //脚本执行时间限制
        $info['execution_time'] = ini_get('max_execution_time').'S';
        //环境
        $sapi = php_sapi_name();
        if($sapi = 'apache2handler') {
        	$info['environment'] = 'apache';
        } elseif($sapi = 'cgi-fcgi') {
        	$info['environment'] = 'cgi';
        } else {
        	$info['environment'] = 'cli';
        }
        //剩余空间大小
        //$info['disk'] = round(disk_free_space("/")/1024/1024,1).'M';
        $this->assign('info',$info);

       //获取文章收录情况
        $shoulu=Db::name('article_generate')->where('shoulu',1)->select();
        $count['pc']=count($shoulu);
        $shoulum=Db::name('article_generate')->where('shoulum',1)->select();
        $count['m']=count($shoulum);
        $count['tot']=$count['pc']+$count['m'];
        $shoulutot=Db::name('article_generate')->select();
        $count['article']=count($shoulutot);
        $this->assign('count',$count);
        /**
         *网站信息
         */
        $web['user_num'] = Db::name('admin')->count();
        $web['admin_cate'] = Db::name('admin_cate')->count();
        $ip_ban = Db::name('webconfig')->value('black_ip');
        $web['ip_ban'] = empty($ip_ban) ? 0 : count(explode(',',$ip_ban));
        
        $web['article_num'] = Db::name('article')->count();
        $web['status_article'] = Db::name('article')->where('status',0)->count();
        $web['top_article'] = Db::name('article')->where('is_top',1)->count();
        $web['file_num'] = Db::name('attachment')->count();
        $web['status_file'] = Db::name('attachment')->where('status',0)->count();
        $web['ref_file'] = Db::name('attachment')->where('status',-1)->count();
        $web['message_num'] = Db::name('messages')->count();
        $web['look_message'] = Db::name('messages')->where('is_look',0)->count();


        //登陆次数和下载次数
        $today = date('Y-m-d');

        //取当前时间的前十四天
        $date = [];
        $date_string = '';
        for ($i=30; $i >0 ; $i--) { 
            $date[] = date("Y-m-d",strtotime("-{$i} day"));
            $date_string.= date("Y-m-d",strtotime("-{$i} day")) . ',';
        }
        $date[] = $today;
        $date_string.= $today;
        $web['date_string'] = $date_string;
        $login_sum = '';
        $article_counts = '';
        $article_shoulu = '';
        foreach ($date as $k => $val) {
            $min_time = strtotime($val);
            $max_time = $min_time + 60*60*24;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        $shoulu=Db::name('article_generate')->where('shoulu',1)->where($where)->select();
        $count['pc']=count($shoulu);
        $shoulum=Db::name('article_generate')->where($where)->where('shoulum',1)->select();
        $count['m']=count($shoulum);
        $count['tot']=$count['pc']+$count['m'];
            $login_sum.=$count['tot'].',';
            //收录数量
            $article_nums=Db::name('article_generate')->where($where)->select();
            $article_counts.=count($article_nums).',';
            //文章数量
            if ($count['tot']!==0&&count($article_nums)!==0) {
                 $article_shoulu.= round($count['tot']/count($article_nums)*100,2).',';
            }else{
                $article_shoulu.='0,';
            }
           
            //收录率
        }
        $web['shoulu'] = $login_sum;
        $web['article'] =$article_counts;
        $web['shoululv'] =$article_shoulu;

        $this->assign('web',$web);
      //获取收录统计数据
        //先查出所有管理员
$admin_sa =Db::name('admin')->order('create_time desc')->select();
$aricle_data_sa=array();
        //第一步获取发出的文章
foreach ($admin_sa as $key => $value) {
   $admin_sa_id=$value['id'];
   $admin_sa_name=$value['nickname'];
   $shoulu_sa=Db::name('article_generate')->where('shoulu',1)->where('admin_id',$admin_sa_id)->select();
   $count_sa['pc']=count($shoulu_sa);
   $shoulum_sa=Db::name('article_generate')->where('admin_id',$admin_sa_id)->where('shoulum',1)->select();
   $count_sa['m']=count($shoulum_sa);
   //获取每个人的收录数量
   $count_sa['shoulu_tot']=$count_sa['pc']+$count_sa['m'];
  
   //获取每个人的文章数量
   $article_num_ci =Db::name('article_generate')->where('admin_id',$admin_sa_id)->select();
   $count_sa['article_tot']=count($article_num_ci);
    // if ($count_sa['article_tot']==0) {
    //     $count_sa['article_tot']=1;
    // }
   $count_sa['name']=$admin_sa_name;
  if ($count_sa['shoulu_tot']!==0||$count_sa['article_tot']!==0) {
     $count_sa['gailv']= round($count_sa['shoulu_tot']/$count_sa['article_tot']*100,2)."%<br />";
  }
   // $count_sa['id']=$admin_sa_id;
//获取线索量
  $xiansuos=Db::name('articlework')->where('admin_id',$admin_sa_id)->select();
  $xiansuo=0;
  if ($xiansuos) {
      foreach ($xiansuos as $key => $value) {
      $xiansuo+=$value['article_xiansuo'];
      }
  };
  $count_sa['xiansuo']=$xiansuo;
   array_push($aricle_data_sa,$count_sa);
};      $aricle_data_sas=$aricle_data_sa;
         array_multisort(array_column($aricle_data_sas,'shoulu_tot'),SORT_DESC,$aricle_data_sas);
          $a=1;
         foreach ($aricle_data_sas as $key => $value) {
            $aricle_data_sas[$key]['desc']=$a;
            $a++;
         }
        $this->assign('shoulu',$aricle_data_sa);
        $this->assign('shoulus',$aricle_data_sas);
        return $this->fetch();
    }
    public function articlecheck(){
        $today = date('Y-m-d');
        //取当前时间的前十四天
       if($this->request->isPost()) {
          //是提交操作
          $post = $this->request->post();
          $id=$post['id'];
          switch ($id) {
              case '0':
                $today = date('Y-m-d');
                $min_time = strtotime($today);
                $max_time = $min_time + 60*60*24;
                $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
                $data['data']=$this->datas($where);
                if ($data) {
                    $data['code']=1;
                    $data['msg']='成功';
                }else{
                    $data['code']=0;
                    $data['msg']='失败';
                }

                return $data;
                  break;
             case '1':
                $today = date("Y-m-d",strtotime("-1 day"));
                $min_time = strtotime($today);
                $max_time = $min_time + 60*60*24;
                $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
                $data['data']=$this->datas($where);
                if ($data) {
                    $data['code']=1;
                    $data['msg']='成功';
                }else{
                    $data['code']=0;
                    $data['msg']='失败';
                }

                return $data;
                  break;     
             case '2':
                $aa=date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
                $bb=date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
                $today = date("Y-m-d",strtotime("-1 day"));
                $min_time = strtotime($aa);
                $max_time = strtotime($bb);
                $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
                $data['data']=$this->datas($where);
                if ($data) {
                    $data['code']=1;
                    $data['msg']='成功';
                }else{
                    $data['code']=0;
                    $data['msg']='失败';
                }

                return $data;
                  break; 
              case '3':
                $cc=date('Y-m-d', strtotime(date('Y-m', time()) . '-01 00:00:00'));
                $dd=date('Y-m-d', strtotime(date('Y-m', time()) . '-' . date('t', time()) . ' 00:00:00'));
                $min_time = strtotime($cc);
                $max_time = strtotime($dd);
                $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
                $data['data']=$this->datas($where);
                if ($data) {
                    $data['code']=1;
                    $data['msg']='成功';
                }else{
                    $data['code']=0;
                    $data['msg']='失败';
                }

                return $data;
                  break; 
               case '4':
                $today=$post['time'];
                $min_time = strtotime($today);
                $max_time = $min_time + 60*60*24;
                $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
                $data['data']=$this->datas($where);
                if ($data) {
                    $data['code']=1;
                    $data['msg']='成功';
                }else{
                    $data['code']=0;
                    $data['msg']='失败';
                }

                return $data;
                  break;                
             default:
                return '0';
                  break;  
                }; 
          }
      }
            
  public function datas($where){
    //查询所有管理员
    $admin_sa =Db::name('admin')->order('create_time desc')->select();
    $article_data=array();
    //查询数据
 foreach ($admin_sa as $key => $value) {
   $admin_id=$value['id'];
   $admin_name=$value['nickname'];
   $shoulu_pc=Db::name('article_generate')->where($where)->where('shoulu',1)->where('admin_id',$admin_id)->select();
   $count['pc']=count($shoulu_pc);
   $shoulu_m=Db::name('article_generate')->where($where)->where('admin_id',$admin_id)->where('shoulum',1)->select();
   $count['m']=count($shoulu_m);
   //获取每个人的收录数量
   $count['shoulu_tot']=$count['pc']+$count['m'];
  
   //获取每个人的文章数量
   $article_num=Db::name('article_generate')->where($where)->where('admin_id',$admin_id)->select();
   $count['article_tot']=count($article_num);
    // if ($count_sa['article_tot']==0) {
    //     $count_sa['article_tot']=1;
    // }
   $count['name']=$admin_name;
  if ($count['shoulu_tot']!==0||$count['article_tot']!==0) {
     $count['gailv']= round($count['shoulu_tot']/$count['article_tot']*100,2)."%";
  }else{
    $count['gailv']= "0%";
  }
   // $count_sa['id']=$admin_sa_id;
  //获取线索量
  $xiansuos=Db::name('articlework')->where($where)->where('admin_id',$admin_id)->select();
  $xiansuo=0;
  if ($xiansuos) {
      foreach ($xiansuos as $key => $value) {
      $xiansuo+=$value['article_xiansuo'];
      }
  };
  $count['xiansuo']=$xiansuo;
   array_push($article_data,$count);
};      
        //进行排序
         array_multisort(array_column($article_data,'shoulu_tot'),SORT_DESC,$article_data);
         //添加排名
          $a=1;
         foreach ($article_data as $key => $value) {
            $article_data[$key]['desc']=$a;
            $a++;
         }
     return $article_data;
        
  }
}
