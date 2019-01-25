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

use \think\Model;
class ArticleGenerate extends Model
{
	
	public function article()
    {
        //关联文章表
        return $this->hasOne('Article');
    }
    public function admin()
    {
        //关联角色表
        return $this->belongsTo('Admin');
    }
     public function getTimetaskAttr($value)
    {
        $value=date('Y-m-d H:i:s',$value);
        return $value;
    }
}
