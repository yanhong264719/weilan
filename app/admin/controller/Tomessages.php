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

use \think\Controller;
use think\Db;
use app\admin\controller\Permissions;
use app\admin\model\Messages;
use app\admin\model\Reply as replys;
class Tomessages extends Permissions
{
    public function index()
    {
        $model = new Messages();
        $models = new replys();

        $post = $this->request->param();
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['message'] = ['like', '%' . $post['keywords'] . '%'];
        }
        
        if (isset($post['is_look']) and ($post['is_look'] == 1 or $post['is_look'] === '0')) {
            $where['is_look'] = $post['is_look'];
        }
 
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['create_time'] = [['>=',$min_time],['<=',$max_time]];
        }
        
        $message = empty($where) ? $model->order('create_time desc')->paginate(5) : $model->where($where)->order('create_time desc')->paginate(5,false,['query'=>$this->request->param()]);
        $info['reply'] = Db::name('reply')->select();
        $info['recruit'] = Db::name('recruit')->select();
        $this->assign('message',$message);
        $this->assign('info',$info);
        return $this->fetch();
    }


    public function publish()
    {
    	$model = new Messages();
		
		//是新增操作
		if($this->request->isPost()) {
			//是提交操作
			$post = $this->request->post();
			//验证  唯一规则： 表名，字段名，排除主键值，主键名
            $validate = new \think\Validate([
                ['message', 'require|length:20,200', '留言不能为空|请输入10-100个字的建议'],
            ]);
            //验证部分数据合法性
            if (!$validate->check($post)) {
                $this->error('提交失败：' . $validate->getError());
            }
            //设置创建人
            $post['ip'] = $this->request->ip();
            if(false == $model->allowField(true)->save($post)) {
            	return $this->error('提交失败');
            } else {
                addlog($model->id);//写入日志
            	return $this->success('提交成功','admin/tomessages/index');
            }
		} else {
			//非提交操作
			return $this->fetch();
		}
    }


    public function mark()
    {
        //获取id
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $model = new Messages();
        //是正常添加操作
        if($id > 0) {
            //是修改操作
            if($this->request->isPost()) {
                //是提交操作
                $post = $this->request->post();
                //验证菜单是否存在
                $message = $model->where('id',$id)->find();
                if(empty($message)) {
                    return $this->error('id不正确');
                }
                if(false == $model->allowField(true)->save($post,['id'=>$id])) {
                    return $this->error('提交失败');
                } else {
                    addlog($model->id);//写入日志
                    return $this->success('提交成功','admin/tomessages/index');
                }
            }
        }
    }


    public function delete()
    {
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
            if(false == Db::name('messages')->where('id',$id)->delete()) {
                return $this->error('删除失败');
            } else {
                Db::name('reply')->where('messages_id',$id)->delete();
                addlog($id);//写入日志
                return $this->success('删除成功','admin/tomessages/index');
            }
    	}
    }
    public function reply(){
        $models = new Messages();
        $model=new replys();
        if($this->request->isAjax()) {
            $id = $this->request->has('messages_id') ? $this->request->param() : 0;
            $mess_id=$model->where('messages_id',$id['messages_id'])->find();
            # 这是修改操作
            if ($mess_id) {
                 if(false == $model->allowField(true)->save($id,['messages_id'=>$id['messages_id']])){
                    return $this->error('修改失败');
                } else {
                    addlog($id);//写入日志
                    return $this->success('修改成功','admin/tomessages/index');
                }
                   
           }//修改操作结束
            else{
            # 这是新增操作   
            $id['admin_id']=input('session.admin');
            $reply_id=$model->allowField(true)->save($id);
            if(!$reply_id) {
                return $this->error('回复失败');
            } else {
                 $re=$model->where('messages_id',$id['messages_id'])->find();
                 $reps_id=['reply_id'=>$re['id']];
                 $models->allowField(true)->save($reps_id,['id'=>$id['messages_id']]);
                addlog($id);//写入日志
                return $this->success('回复成功','admin/tomessages/index');
            }
        }

        }//新增操作结束
}
}