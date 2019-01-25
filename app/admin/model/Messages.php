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
class Messages extends Model
{
	public function recruit(){
		return $this->belongsTO('Recruit');
	}
	public function reply(){
		return $this->belongsTO('Reply');
	}
	public function admin(){
		return $this->belongsTO('Admin');
	}
}
