<?php


namespace app\admin\controller;

use \think\Cache;
use \think\Controller;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
use app\admin\model\Articlework as articleworks;
use app\admin\controller\Permissions;
class Articlework extends Permissions
{
    public function index()
    {
        $model=new articleworks();
        $post = $this->request->param();

        if (isset($post['admin_id']) and $post['admin_id'] > 0) {
            $where['admin_id'] = $post['admin_id'];
        }
 
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
        
        $articles = empty($where) ? $model->order('create_time desc')->paginate(20) :$model->where($where)->order('create_time desc')->paginate(20,false,['query'=>$this->request->param()]);

        //$articles = $article->toArray();
        //添加最后修改人的name
         foreach ($articles as $key => $value) {
            $articles[$key]['edit_admin'] = Db::name('admin')->where('id',$value['edit_admin_id'])->value('nickname');
        }
      
        $this->assign('articles',$articles);
        $info['admin'] = Db::name('admin')->select();
        $this->assign('info',$info);
        return $this->fetch();
    }


    public function publish()
    {   
         $model=new articleworks();
    	//获取菜单id
    	$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
		//是正常添加操作
		if($id > 0) {
    		//是修改操作
    		if($this->request->isPost()) {
    			//是提交操作
    			$post = $this->request->post();
    			//验证  唯一规则： 表名，字段名，排除主键值，主键名
	            $validate = new \think\Validate([
	                ['article_xiansuo', 'require', '线索量不能为空'],
                    ['admin_id', 'require', '请选择分类'],
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
                   
	            	return $this->success('修改成功','admin/articlework/index');
	            }
    		} else {
    			//非提交操作
                $cate = Db::name('admin')->select();
                $this->assign('admin',$cate);
                $cate = Db::name('admin')->select();
                $this->assign('cates',$cate);
    			$article = $model->where('id',$id)->find();
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
	                ['article_xiansuo', 'require', '线索量不能为空'],
                    ['admin_id', 'require', '请选择分类'],
                   
	            ]);
	            //验证部分数据合法性
	            if (!$validate->check($post)) {
	                $this->error('提交失败：' . $validate->getError());
	            }
                //设置创建人
            
                //设置修改人
                $post['edit_admin_id'] = Session::get('admin');
	            if(false ==$model->allowField(true)->save($post)) {
	            	return $this->error('添加失败');
	            } else {
                   
	            	return $this->success('添加成功','admin/articlework/index');
	            }
    		} else {
    			//非提交操作
    			$cate = Db::name('admin')->select();
    			$this->assign('cates',$cate);
    			return $this->fetch();
    		}
    	}
    	
    }


    public function delete()
    {
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('articlework')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                addlog($id);//写入日志
                return $this->success('删除成功','admin/articlework/index');
            }
    	}
    }

}
