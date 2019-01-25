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
class Recruit extends Model
{
	public function cate()
    {
        //关联分类表
        return $this->belongsTo('Recruitinfo');
    }

    public function admin()
    {
        //关联角色表
        return $this->belongsTo('Admin');
    }
    public function ment()
    {
        //关缩昵图
        return $this->belongsTo('Attachment');
    }
}
