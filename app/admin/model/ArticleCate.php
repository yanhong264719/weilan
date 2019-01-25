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


namespace app\admin\model;
use think\Db;
use app\admin\model\ArticleCate as cateModel;
use \think\Model;
class ArticleCate extends Model
{
	public function catelist($cate,$id=0,$level=0){
		static $cates = array();
		foreach ($cate as $value) {
			if ($value['pid']==$id) {
				$value['level'] = $level+1;
				if($level == 0)
				{
					$value['str'] = str_repeat('',$value['level']);
				}
				elseif($level == 2)
				{
					$value['str'] = '&emsp;&emsp;&emsp;&emsp;'.'└ ';
				}
				else
				{
					$value['str'] = '&emsp;&emsp;'.'└ ';
				}
				$cates[] = $value;
				$this->catelist($cate,$value['id'],$value['level']);
			}
		}
		return $cates;
	}


	public function article()
    {
        //关联文章表
        return $this->hasOne('Article');
    }
    //处理id转名称
    public function education($id){
    	$cateModel = new cateModel();
        $articlecateer_pid=$cateModel->field('id,name,pid')->where('id',$id)->find();
         $catedata['san'] =$articlecateer_pid['name'];
	      $cateer_pid_pid = $articlecateer_pid['pid'];
	     $articlecatesan_pid=$cateModel->field('id,name,pid')->where('id',$cateer_pid_pid)->find();
	      $catedata['er'] =$articlecatesan_pid['name'];
	      $catesan_pid_pid = $articlecatesan_pid['pid'];
	      $articlecatesi_pid=$cateModel->field('id,name,pid')->where('id',$catesan_pid_pid)->find();
	      $catedata['yi'] =$articlecatesi_pid['name'];
	      $data=$catedata['er'].'-'.$catedata['san'];
	      return $data;
    }
}
