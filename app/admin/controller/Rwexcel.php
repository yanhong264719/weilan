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
use think\Validate;
use app\admin\controller\Permissions;
use app\admin\model\Article as articleModel;
use app\admin\model\ArticleCate as cateModel;
use app\admin\model\ArticleGenerate as generateModel;
class Rwexcel extends Permissions
{   
    public function index()
    {   
        return $this->fetch(); 
    }
    public function fabu()
    {   
        return $this->fetch(); 
    }
    public function titles()
    {
       
        return $this->fetch();
    }
        //导出xls
    public function daochu()
    {
        $session_a=input('session.admin/d');
        $model = new generateModel();
        $post = $this->request->param();
        $number=$post['number'];
        $times=$post['timeout'];
        if ($session_a==1){//超级管理员
            if ($times) {//有时间限制
            $explode = explode('~',$times);
            $min_time=$explode[0];
            $max_time=$explode[1];
            $min_time= strtotime($min_time);
            $max_time= strtotime($max_time);
            // echo date('Y-m-d H:i:s',$min_time);
            // echo '<br/>';
            // echo date('Y-m-d H:i:s',$max_time);
            // echo '<br/>';
            $where['create_time'] = [['>=',$min_time],['<',$max_time]];
            if ($number) {
               $sql=Db::name('article_generate')->order('create_time desc')->where($where)->where('shoulu|shoulum','eq',1)->limit($number)->select();
               // foreach ($sql as  $value) {
               //    $time=$value['create_time'];
               //    echo date('Y-m-d H:i:s',$time);
               //    echo '<br/>';
               // }
            }else{
               $sql=Db::name('article_generate')->order('create_time desc')->where($where)->where('shoulu|shoulum','eq',1)->select();
            }
            //判断权限
           
            }else{//没有时间限制
                if ($number) {
               $sql=Db::name('article_generate')->order('create_time desc')->where('shoulu',1)->whereOr('shoulum',1)->limit($number)->select();
            }else{
               $sql=Db::name('article_generate')->order('create_time desc')->where('shoulu',1)->whereOr('shoulum',1)->select();
            }
            }
        }else{//非超级管理员
              if ($times) {//有时间限制
                $explode = explode('~',$times);
                $min_time=$explode[0];
                $max_time=$explode[1];
                $min_time= strtotime($min_time);
                $max_time= strtotime($max_time);
                $where['create_time'] = [['>=',$min_time],['<',$max_time]];
                if ($number) {
                   $sql=Db::name('article_generate')->order('create_time desc')->where('shoulu|shoulum','eq',1)->where('admin_id','eq',$session_a)->where($where)->limit($number)->select();
                }else{
                  $sql=Db::name('article_generate')->order('create_time desc')->where('shoulu|shoulum','eq',1)->where('admin_id','eq',$session_a)->where($where)->select();
                }
                //判断权限
                }else{//没有时间限制
                    if ($number) {
                       $sql=Db::name('article_generate')->order('create_time desc')->where('shoulu|shoulum','eq',1)->where('admin_id','eq',$session_a)->limit($number)->select();
                    }else{
                      $sql=Db::name('article_generate')->order('create_time desc')->where('shoulu|shoulum','eq',1)->where('admin_id','eq',$session_a)->select();
                       }
                   }
              }
              //判断完成获取数据了
            //2.加载PHPExcle类库
        if ($sql) {
            vendor('PHPExcel.PHPExcel');
            //3.实例化PHPExcel类
            $objPHPExcel = new \PHPExcel();
            //4.激活当前的sheet表
            $objPHPExcel->setActiveSheetIndex(0);
            //5.设置表格头（即excel表格的第一行）
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'ID')
                    ->setCellValue('B1', '标题')
                    ->setCellValue('C1', '电脑网站地址')
                    ->setCellValue('D1', '手机网站地址')
                    ->setCellValue('E1', '电脑收录')
                    ->setCellValue('F1', '手机收录');
            //设置A列水平居中
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置单元格宽度
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30); 
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(50); 
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(50); 
            //6.循环刚取出来的数组，将数据逐一添加到excel表格。
            for($i=0;$i<count($sql);$i++){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$sql[$i]['id']);//ID
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$sql[$i]['title']);//标题
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$sql[$i]['urlpc']);//电脑网站
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$sql[$i]['urlm']);//手机网站
                $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$sql[$i]['shoulu']);//电脑收录
                $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$sql[$i]['shoulum']);//手机收录
            }
            //7.设置保存的Excel表格名称
            $filename = date('Y-m-d H:i:s',time()).'.xls';
            //8.设置当前激活的sheet表格名称；
            $objPHPExcel->getActiveSheet()->setTitle('收录信息表');
            //9.设置浏览器窗口下载表格
            ob_end_clean();//清除缓冲区,避免乱码
            header("Content-Type: application/force-download");  
            header("Content-Type: application/octet-stream");  
            header("Content-Type: application/download");  
            header('Content-Disposition:inline;filename="'.$filename.'"');  
            //生成excel文件
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            //下载文件在浏览器窗口
            $objWriter->save('php://output');
       }else{
        return $this->error('抱歉,没查到数据');
       }
       return $this->fetch();
   }
   public function daochufabu()
    {
        $session_a=input('session.admin/d');
        $model = new generateModel();
        $post = $this->request->param();
        $number=$post['number'];
        $times=$post['timeout'];
        if ($session_a==1){//超级管理员
            if ($times) {//有时间限制
            $explode = explode('~',$times);
            $min_time=$explode[0];
            $max_time=$explode[1];
            $min_time= strtotime($min_time);
            $max_time= strtotime($max_time);
            // echo date('Y-m-d H:i:s',$min_time);
            // echo '<br/>';
            // echo date('Y-m-d H:i:s',$max_time);
            // echo '<br/>';
            $where['create_time'] = [['>=',$min_time],['<',$max_time]];
            if ($number) {
               $sql=Db::name('article_generate')->order('create_time desc')->where($where)->limit($number)->select();
               // foreach ($sql as  $value) {
               //    $time=$value['create_time'];
               //    echo date('Y-m-d H:i:s',$time);
               //    echo '<br/>';
               // }
            }else{
               $sql=Db::name('article_generate')->order('create_time desc')->where($where)->select();
            }
            //判断权限
           
            }else{//没有时间限制
                if ($number) {
               $sql=Db::name('article_generate')->order('create_time desc')->limit($number)->select();
            }else{
               $sql=Db::name('article_generate')->order('create_time desc')->select();
            }
            }
        }else{//非超级管理员
              if ($times) {//有时间限制
                $explode = explode('~',$times);
                $min_time=$explode[0];
                $max_time=$explode[1];
                $min_time= strtotime($min_time);
                $max_time= strtotime($max_time);
                $where['create_time'] = [['>=',$min_time],['<',$max_time]];
                if ($number) {
                   $sql=Db::name('article_generate')->order('create_time desc')->where('admin_id','eq',$session_a)->where($where)->limit($number)->select();
                }else{
                  $sql=Db::name('article_generate')->order('create_time desc')->where('admin_id','eq',$session_a)->where($where)->select();
                }
                //判断权限
                }else{//没有时间限制
                    if ($number) {
                       $sql=Db::name('article_generate')->order('create_time desc')->where('admin_id','eq',$session_a)->limit($number)->select();
                    }else{
                      $sql=Db::name('article_generate')->order('create_time desc')->where('admin_id','eq',$session_a)->select();
                       }
                   }
              }
              //判断完成获取数据了
            //2.加载PHPExcle类库
        if ($sql) {
            vendor('PHPExcel.PHPExcel');
            //3.实例化PHPExcel类
            $objPHPExcel = new \PHPExcel();
            //4.激活当前的sheet表
            $objPHPExcel->setActiveSheetIndex(0);
            //5.设置表格头（即excel表格的第一行）
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'ID')
                    ->setCellValue('B1', '标题')
                    ->setCellValue('C1', '电脑网站地址')
                    ->setCellValue('D1', '手机网站地址');
                   
            //设置A列水平居中
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置单元格宽度
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30); 
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(50); 
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(50); 
            //6.循环刚取出来的数组，将数据逐一添加到excel表格。
            for($i=0;$i<count($sql);$i++){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$sql[$i]['id']);//ID
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$sql[$i]['title']);//标题
                $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$sql[$i]['urlpc']);//电脑网站
                $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$sql[$i]['urlm']);//手机网站
            }
            //7.设置保存的Excel表格名称
            $filename = date('Y-m-d H:i:s',time()).'.xls';
            //8.设置当前激活的sheet表格名称；
            $objPHPExcel->getActiveSheet()->setTitle('文章发布信息表');
            //9.设置浏览器窗口下载表格
            ob_end_clean();//清除缓冲区,避免乱码
            header("Content-Type: application/force-download");  
            header("Content-Type: application/octet-stream");  
            header("Content-Type: application/download");  
            header('Content-Disposition:inline;filename="'.$filename.'"');  
            //生成excel文件
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            //下载文件在浏览器窗口
            $objWriter->save('php://output');
       }else{
        return $this->error('抱歉,没查到数据');
       }
       return $this->fetch();
   }
   public function example()
    {
       
        $model = new generateModel();
        $sql=Db::name('title')->limit(20)->select();
              //判断完成获取数据了
            //2.加载PHPExcle类库
        if ($sql) {
            vendor('PHPExcel.PHPExcel');
            //3.实例化PHPExcel类
            $objPHPExcel = new \PHPExcel();
            //4.激活当前的sheet表
            $objPHPExcel->setActiveSheetIndex(0);
            //5.设置表格头（即excel表格的第一行）
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'ID')
                    ->setCellValue('B1', '关键字');
            //设置A列水平居中
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置单元格宽度
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30); 
            //6.循环刚取出来的数组，将数据逐一添加到excel表格。
            for($i=0;$i<count($sql);$i++){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$sql[$i]['id']);//ID
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$sql[$i]['value']);//标题
            }
            //7.设置保存的Excel表格名称
            $filename = date('Y-m-d H:i:s',time()).'.xls';
            //8.设置当前激活的sheet表格名称；
            $objPHPExcel->getActiveSheet()->setTitle('标题关键字信息表');
            //9.设置浏览器窗口下载表格
            ob_end_clean();//清除缓冲区,避免乱码
            header("Content-Type: application/force-download");  
            header("Content-Type: application/octet-stream");  
            header("Content-Type: application/download");  
            header('Content-Disposition:inline;filename="'.$filename.'"');  
            //生成excel文件
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            //下载文件在浏览器窗口
            $objWriter->save('php://output');
       }else{
        return $this->error('抱歉,数据出现问题');
       }
       return $this->fetch();

    }
   
    public function excel()
    {
        vendor("PHPExcel.PHPExcel"); //方法一
        $objPHPExcel = new \PHPExcel();
        //获取表单上传文件
        $validate=new Validate;
        $file = request()->file('file');
        $info = $file->validate(['size'=>300000,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'excel');
        if($info){
        //获取文件名
        $exclePath = $info->getSaveName();
        //上传文件的地址
        $file_name = ROOT_PATH . 'public' . DS . 'excel' . DS . $exclePath;
        //判断文件名
        $extension = strtolower( pathinfo($file_name, PATHINFO_EXTENSION) );
        //判断文件后缀
        if ($extension =='xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
            $objExcel = $objReader ->load($file_name,$encode = 'utf-8');
        } else if ($extension =='xls') {
            $objReader = new \PHPExcel_Reader_Excel5();
            $objExcel = $objReader ->load($file_name,$encode = 'utf-8');
        } else if ($extension=='csv') {
            $PHPReader = new \PHPExcel_Reader_CSV();

            //默认输入字符集
            $PHPReader->setInputEncoding('utf8');

            //默认的分隔符
            $PHPReader->setDelimiter(',');

            //载入文件
            $objExcel = $PHPReader->load($file_name);
        }

        // $sheet = $objExcel ->getSheet(0)->toArray();
        // var_dump($sheet);
        // $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        // //加载文件内容,编码utf-8
        // $obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');

        $excel_array = $objExcel->getsheet(0)->toArray(); //转换为数组格式
        array_shift($excel_array); //删除第一个数组(标题);
        $data = [];
        foreach ($excel_array as $k => $v) {
        $data[$k]['value'] = $v['1'];
        if ($data[$k]['value']==null) {
            unset($data[$k]);
        }
        }
        // array_pop($data);
        //批量插入数据
        $success = Db::name('title')->insertAll($data);
         $datas['code']=1;
             $datas['msg']='上传成功';
             $datas['data']='成功上传'.$success.'数据';
             $datas['url']=input('server.SERVER_NAME').url();
             $datas['wait']=3;
             return $datas;
        }else{
        // 上传失败获取错误信息
        // 上传失败获取错误信息
             $data['code']=0;
             $data['msg']=$file->getError();
             $data['data']='从新上传后缀为xlsx、xls、csv';
             $data['url']=input('server.SERVER_NAME').url();
             $data['wait']=3;
             return $data;
        }
  }


}
