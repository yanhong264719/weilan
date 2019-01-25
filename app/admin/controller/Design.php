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
use app\admin\model\Design as designs;
class Design extends Permissions
{
    public function liebiao()
    {
       
        // $articles = empty($where) ? $model->order('create_time desc')->paginate(20) : $model->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);

        // $this->assign('articles',$articles);
        // $info['cate'] = Db::name('article_cate')->select();
        // $info['admin'] = Db::name('admin')->select();
        $liebiao="fdsfds";
        $this->assign('liebiao',$liebiao);
        return $this->fetch();
    }

   public function upload()
    {
       
    // 获取表单上传文件 
    $file = request()->file('file');
    // 移动到框架应用根目录/public/uploads/ 目录下
    if($file){
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
             $data['code']=1;
             $data['msg']='上传成功';
             $data['data']=$name;
             $data['url']=input('server.SERVER_NAME').url();
             $data['wait']=3;
            return $data;          
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

   //处理表单数据

   // 显示上传页面
    return $this->fetch();
    }
    public function uploads()
    {
       
    // 获取表单上传文件 
    $file = request()->file('file');
    // 移动到框架应用根目录/public/uploads/ 目录下
    if($file){
        $info = $file->validate(['ext'=>'zip'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
             $data['code']=1;
             $data['msg']='上传成功';
             $data['data']=$info->getFilename();
             $data['url']=input('server.SERVER_NAME').url();
             $data['wait']=3;
            return $data;          
        }else{
            // 上传失败获取错误信息
             $data['code']=0;
             $data['msg']='上传失败';
             $data['data']='从新上传后缀为zip的文件';
             $data['url']=input('server.SERVER_NAME').url();
             $data['wait']=3;
            return $data;        
        }
    }

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
	                ['article_cate_id', 'require', '请选择分类'],
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
	            	return $this->success('修改成功','admin/article/index');
	            }
    		} else {
    			//非提交操作
    			$article = $model->where('id',$id)->find();
    			$cates = $cateModel->select();
    			$cates_all = $cateModel->catelist($cates);
    			$this->assign('cates',$cates_all);
    			if(!empty($article)) {
    				$this->assign('article',$article);
    				return $this->fetch();
    			} else {
    				return $this->error('id不正确');
    			}
    		}
    	} else {
    		//是新增操作
    		if($this->request->isPost()) {
    			//是提交操作
    			$post = $this->request->post();
    			//验证  唯一规则： 表名，字段名，排除主键值，主键名
	            $validate = new \think\Validate([
	                ['title', 'require', '标题不能为空'],
                    ['article_cate_id', 'require', '请选择分类'],
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
	            	return $this->success('添加成功','admin/article/index');
	            }
    		} else {
    			//非提交操作
    			$cate = $cateModel->select();
    			$cates = $cateModel->catelist($cate);
    			$this->assign('cates',$cates);
    			return $this->fetch();
    		}
    	}
    	
    }


    public function delete()
    {
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('article')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                addlog($id);//写入日志
                return $this->success('删除成功','admin/article/index');
            }
    	}
    }


    public function is_top()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('article')->where('id',$post['id'])->update(['is_top'=>$post['is_top']])) {
                return $this->error('设置失败');
            } else {
                addlog($post['id']);//写入日志
                return $this->success('设置成功','admin/article/index');
            }
        }
    }


    public function status()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            if(false == Db::name('article')->where('id',$post['id'])->update(['status'=>$post['status']])) {
                return $this->error('设置失败');
            } else {
                addlog($post['id']);//写入日志
                return $this->success('设置成功','admin/article/index');
            }
        }
    }
}
