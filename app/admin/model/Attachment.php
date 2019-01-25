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
class Attachment extends Model
{
    public function admin()
    {
        //关联管理员表
        return $this->belongsTo('Admin');
    }
  
    public function recruits()
    {

        //关联管理员表
        return $this->belongsTo('Recruit');
    }
}
