<?php
namespace app\api\controller\v1;

use \think\Cache;
use \think\Controller;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
use \think\Request;
use app\index\controller\Checksystems;
use app\admin\model\Recruit;
use app\admin\model\Article;
use app\admin\model\Recruitinfo;
use app\admin\model\Messages;
use app\api\model\Users as UserModel;
class User extends Checksystems
{
    public function read($id = 0)
    {
        $user = UserModel::get($id);
        if ($user) {
            return json($user);
        } else {
            return json(['error' => '用户不存在'], 404);
        }
    }
}