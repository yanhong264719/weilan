<?php
namespace app\index\controller;

use \think\Cache;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
use \think\Request;
use app\index\controller\Checksystems;
use app\index\model\Recruit;
use app\admin\model\Recruitinfo;
class Work extends Checksystems
{
    public function work()
    {
         
        $model = new Recruit();
        $post = $this->request->param();
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['title'] = ['like', '%' . $post['keywords'] . '%'];
        }
        if (isset($post['city'])and !empty($post['city'])) {
            $where['city'] = $post['city'];
            $this->assign('city',$post['city']);
        }

        if (isset($post['asale']) and $post['asale'] > 0) {
        	switch ($post['asale']) {
        		case '100':
        			$where['asale'] = ['>=',100];

        			 $this->assign('asale',$post['asale']);
        			break;
        		case '3000':
        			$where['asale'] = ['<=',3000];

        			 $this->assign('asale',$post['asale']);
        			break;
        			
        		case '4000':
        			$where['asale'] = [['>=',3000],['<',4000]];
        			 $this->assign('asale',$post['asale']);
        			break;

        		case '5000':
        			$where['asale'] = [['>=',4000],['<',5000]];
        			 $this->assign('asale',$post['asale']);
        			break;

        		case '6000':
        			$where['asale'] = [['>=',5000],['<',6000]];
        			 $this->assign('asale',$post['asale']);
        			break;

        		case '7000':
        			$where['asale'] = ['>=',6000];
        			 $this->assign('asale',$post['asale']);
        			break;				
        		
        		default:
        			$this->error('查询不到该值','index/work/work');
        			break;
        	}
          
        }
        
        if (isset($post['recruitinfo_id'])and !empty($post['recruitinfo_id'])) {
        	switch ($post['recruitinfo_id']) {
        		case '101':
        			$where['recruitinfo_id'] =['>',1];
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '储备干部':
        			$where['recruitinfo_id'] =1;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '长期工':
        			$where['recruitinfo_id'] =4;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '小时工':
        			$where['recruitinfo_id'] =5;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '安全乘务员':
        			$where['recruitinfo_id'] =6;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '派出所辅警':
        			$where['recruitinfo_id'] =7;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '普工':
        			$where['recruitinfo_id'] =8;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '客服':
        			$where['recruitinfo_id'] =9;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '司机':
        			$where['recruitinfo_id'] =10;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '技工':
        			$where['recruitinfo_id'] =11;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;
        		case '销售':
        			$where['recruitinfo_id'] =12;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;									
        		case '其他':
        			$where['recruitinfo_id'] =13;
        			$this->assign('recruitinfo_id',$post['recruitinfo_id']);
        			break;	
        		default:
        			$this->error('查询不到该值','index/work/work');
        			break;
        	}
            
        }

        if (isset($post['tag']) and !empty($post['tag'])) {
        	if ($post['tag']!=='102') {
        	$where['tag'] = ['like', '%' . $post['tag'] . '%'];
            $this->assign('tag',$post['tag']);
        	}
           
        }
 
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
        
         $data = empty($where) ? $model->order('create_time desc')->paginate(5) : $model->where($where)->order('create_time desc')->paginate(5,false,['query'=>$this->request->param()]);
         if ($data) {
         $info['total']=$data->toArray()['total'];
         $this->assign('data',$data);
         $info['top']=$model->where('status',1)->order('create_time desc')->limit(0,5)->select();
         $info['right']=$model->where('status',1)->where('is_top',1)->limit(9)->select();
         $info['bottom']=$model->where('status',1)->limit(5)->select();
         $this->assign('info',$info);
         }else{
         	$data=$model->order('create_time desc')->paginate(5);
         	$info['total']=$data->toArray()['total'];
            $this->assign('data',$data);
            $this->assign('info',$info);
         }
        
         return view();
    }
    // public function city(){
    //     $model = new Recruit();
    //     $post = $this->request->param();
    //     if (isset($post['city'])) {
    //         $where['city'] = $post['city'];
    //         $city=$post['city'];
    //     if ($post) {
    //     	$post['code']=1;
    //     	$post['msg']='切换到'.$city;
    //     }else{
    //     	$post['code']=0;
    //     	$post['msg']='地区切换失败';
    //     }
    //     }
       
    //     $data=$model->where('city',$city)->order('create_time desc')->select();
    //     $this->assign('data',$data);
    //     return $post;
    //     $this->display('work');


    // }
    // public function around(){
    // 	 $get= $this->request->param();
    // 	 $model = new Recruit();
    //      $data =$model->where('city',$get['city'])->where('status',1)->paginate(1);
    //      ;
    //      var_dump($model->getLastSql());
    //      $info['total']=$data->toArray()['total'];
    //      $this->assign('data',$data);
    //      $this->assign('info',$info);
    //     return  $this->fetch('work');
    //      //return $get;
    // }
}
