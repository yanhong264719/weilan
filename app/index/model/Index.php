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


namespace app\index\model;

use \think\Model;
class Index extends Model
{
	public function zhaopin()
    {
        //关联招聘信息表
        return $this->hasOne('Recruit');
    }


    public function article()
    {
        //关联文章表
        return $this->hasOne('Article');
    }

    public function log()
    {
        //关联日志表
        return $this->hasOne('AdminLog');
    }

    public function attachment()
    {
        //关联附件表
        return $this->hasOne('Attachment');
    }
}
