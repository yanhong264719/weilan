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

use \think\Cache;
use \think\Controller;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
use app\admin\controller\Permissions;
use app\admin\model\Recruit as articleModel;
use app\admin\model\Recruitinfo as cateModel;
class Recruit extends Permissions
{
    public function index()
    {
        $model = new articleModel();
        $post = $this->request->param();
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['title'] = ['like', '%' . $post['keywords'] . '%'];
        }
        if (isset($post['recruitinfo_id']) and $post['recruitinfo_id'] > 0) {
            $where['recruitinfo_id'] = $post['recruitinfo_id'];
        }

        if (isset($post['admin_id']) and $post['admin_id'] > 0) {
            $where['admin_id'] = $post['admin_id'];
        }
        
        if (isset($post['status']) and ($post['status'] == 1 or $post['status'] === '0')) {
            $where['status'] = $post['status'];
        }

        if (isset($post['is_top']) and ($post['is_top'] == 1 or $post['is_top'] === '0')) {
            $where['is_top'] = $post['is_top'];
        }
 
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
        
         $articles = empty($where) ? $model->order('create_time desc')->paginate(20) : $model->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);

        //$articles = $article->toArray();
        //添加最后修改人的name
        foreach ($articles as $key => $value) {
            $articles[$key]['edit_admin'] = Db::name('admin')->where('id',$value['edit_admin_id'])->value('nickname');
        }
        $this->assign('articles',$articles);
        $info['cate'] = Db::name('recruitinfo')->select();
        //var_dump($info['cate']);
        $info['admin'] = Db::name('admin')->select();
        $this->assign('info',$info);
        return $this->fetch();
    }
 

    public function publish()
    {
        //获取菜单id
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $model = new articleModel();
        $cateModel = new cateModel();
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '标题不能为空'],
                    ['recruitinfo_id', 'require', '请选择分类'],
                    ['thumb', 'require', '请上传缩略图'],
                    ['content', 'require', '文章内容不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //验证菜单是否存在
                $article = $model->where('id',$id)->find();
                if(empty($article)) {
                    return $this->error('id不正确');
                }
                //设置修改人
                $post['edit_admin_id'] = Session::get('admin');
                if(false == $model->allowField(true)->save($post,['id'=>$id])) {
                    return $this->error('修改失败');
                } else {
                    addlog($model->id);//写入日志
                    return $this->success('修改成功','admin/recruit/index');
                }
            } else {
                //非提交操作
                $recruits= $model->where('id',$id)->find();
                $cates = $cateModel->select();
                $cates_all = $cateModel->catelist($cates);
                $this->assign('cates',$cates_all);
                if(!empty($recruits)) {
                    $this->assign('recruits',$recruits);
                    $pictures=$recruits['pictures'];
                    $pictures=explode(",",$pictures);
                    $this->assign('pictures',$pictures);
                    return $this->fetch();
                } else {
                    return $this->error('id不正确');
                }
            }
        } //修改操作结束
        else {
            //是新增操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['title', 'require', '标题不能为空'],
                    ['recruitinfo_id', 'require', '请选择分类'],
                    ['thumb', 'require', '请上传缩略图'],
                    ['content', 'require', '文章内容不能为空'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                //设置创建人
                $post['admin_id'] = Session::get('admin');
                //设置修改人
                $post['edit_admin_id'] = $post['admin_id'];
                if(false == $model->allowField(true)->save($post)) {
                    return $this->error('添加失败');
                } else {
                    addlog($model->id);//写入日志
                    return $this->success('添加成功','admin/recruit/index');
                }
            } else {
                //非提交操作
                $cate = $cateModel->select();
                $cates = $cateModel->catelist($cate);
                $this->assign('cates',$cates);
                return $this->fetch();
            }
        }//新增操作结束
        
    }


    public function delete()
    {
        if($this->request->isAjax()) {
            $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('recruit')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                addlog($id);//写入日志
                return $this->success('删除成功','admin/recruit/index');
            }
        }
    }


    public function is_top()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('recruit')->where('id',$post['id'])->update(['is_top'=>$post['is_top']])) {
                return $this->error('设置失败');
            } else {
                addlog($post['id']);//写入日志
                return $this->success('设置成功','admin/recruit/index');
            }
        }
    }


    public function status()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('recruit')->where('id',$post['id'])->update(['status'=>$post['status']])) {
                return $this->error('设置失败');
            } else {
                addlog($post['id']);//写入日志
                return $this->success('设置成功','admin/recruit/index');
            }
        }
    }

    //多图片上传方法
    public function uploadajax(){
        
    $typeArr = array("jpg","jpeg","png", "gif","ico");
    //允许上传文件格式
    $path = "static/uploadajax/";
    //上传路径

    // if (!file_exists($path)) {
    //   mkdir($path,0777);
    // }

    if (isset($_POST)) {
        $name = $_FILES['file']['name'];
        $size = $_FILES['file']['size'];
        $name_tmp = $_FILES['file']['tmp_name'];
        if (empty($name)) {
            echo json_encode(array("error" => "您还未选择图片"));
            exit ;
        }
        $type = strtolower(substr(strrchr($name, '.'), 1));
        //获取文件类型

        if (!in_array($type, $typeArr)) {
            echo json_encode(array("error" => "清上传jpg,png或gif类型的图片！"));
            exit ;
        }
        if ($size > (500 * 1024)) {
            echo json_encode(array("error" => "图片大小已超过500KB！"));
            exit ;
        }

        $pic_name = time() . rand(10000, 99999) . "." . $type;
        //图片名称
        $pic_url = $path . $pic_name;
        //上传后图片路径+名称
        if (move_uploaded_file($name_tmp, $pic_url)) {
        //临时文件转移到目标文件夹
            $pic_url = '/'.$path . $pic_name;
            echo json_encode(array("error" => "0", "pic" => $pic_url, "name" => $pic_name));
        } else {
            echo json_encode(array("error" => "上传有误，清检查服务器配置！"));
        }
    }
 }
}
