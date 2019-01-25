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
namespace app\common\model\contrab;
use app\admin\model\ArticleGenerate as generateModel;
use think\Db;
use \think\Cookie;
use \think\Session;
class Embody
{   
   public function shoulustart(){
	  ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
      set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
       $this->shouludatapc();
       $this->shouludatam();
        return '1';
   }   
   public function baiducheck($url){
    
    $url='http://www.baidu.com/s?wd='.$url;
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $rs=curl_exec($curl);
    curl_close($curl);
    if(!strpos($rs,'没有找到该URL。您可以直接访问') && !strpos($rs,'很抱歉，没有找到与') ){
        return 1;
    } else {
        return 0;
    }
    
  }
  public function shouludatapc(){
	  //&&taskkill /f /t /im cmd.exe
	    ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
       set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
	try{
       $model = new generateModel();
       $article_generate=Db::name('article_generate')->where('shoulu',0)->where('status',200)->where('checknumbers','elt',100)->select();
       foreach ($article_generate as $article) {
            $urls=$article['urlpc'];
			$datas=date('Y-m-d H:i:s',time());
			if($urls!==''&&$urls!=='未发布'){
			$id=$article['id'];
            $sta=$this->baiducheck($urls);
            if ($sta==1) {
             $update=1;
			 $oka=Db::name('article_generate')->where('id', $id)->update(['shoulu' =>$update]);
			 $this->CreateXml($urls,$datas);
            }else{
			 $checknumbers=Db::name('article_generate')->where('id',$id)->find();
			 $checknumbers_cishu=$checknumbers['checknumbers'];
			 $update=1+$checknumbers_cishu;
			 Db::name('article_generate')->where('id', $id)->update(['checknumbers' =>$update]);
			}
			
			}
          sleep(1);
			
        }
	}catch(\Exception $e){
		$datasa=date('Y-m-d H:i:s',time());
		$err=$e->getMessage();
		$this->CreateXml($err,$datasa);
	}
		
  }
   public function shouludatam(){
	   ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
      set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
  try{
       $model = new generateModel();
       $article_generate=Db::name('article_generate')->where('shoulum',0)->where('status',200)->where('checknumbers','elt',100)->select();
       foreach ($article_generate as $article) {
            $urls=$article['urlm'];
			$datas=date('Y-m-d H:i:s',time());
			if($urls!==''&&$urls!=='未发布'){
            $id=$article['id'];
            $sta=$this->baiducheck($urls);
            if ($sta==1) {
            $update=1;
			 $oka=Db::name('article_generate')->where('id', $id)->update(['shoulum' =>$update]);
			  $this->CreateXml($urls,$datas);
            }else{
			$checknumbers=Db::name('article_generate')->where('id',$id)->find();
			 $checknumbers_cishu=$checknumbers['checknumbers'];
			 $update=1+$checknumbers_cishu;
			 Db::name('article_generate')->where('id', $id)->update(['checknumbers' =>$update]);
			}
			}
			
			sleep(1);
        }
		}catch(\Exception $e){
		$datasa=date('Y-m-d H:i:s',time());
		$err=$e->getMessage();
		$this->CreateXml($err,$datasa);
	}
  }
  public function CreateXml($title_value,$author_value,$body_value='Always'){     
			$xmlpath = "sitemap.xml";
			$dom = new \DomDocument('1.0','utf-8');
			$dom->preserveWhiteSpace = false;
            $dom->formatOutput = TRUE;
			// $dom->load($xmlpath);
			// $tag = $dom->getElementsByTagName("title");
			if (file_exists($xmlpath)) {
				# 如果文件存在，则进行追加
				$dom->formatOutput = true;
				$dom->load($xmlpath);
				$newarticles = $dom->createElement('url');
				$articles = $dom->getElementsByTagName("urlset")->item(0);  //找到文件追加的位置
				$articles->appendChild($newarticles);				//进行文件追加
 
				$title = $dom->createElement('loc');
				$title->appendChild($dom->createTextNode($title_value));
				$newarticles->appendChild($title);
              
				$author = $dom->createElement('lastmod');
				$author->appendChild($dom->createTextNode($author_value));
				$newarticles->appendChild($author);
                
				$body = $dom->createElement('changefreq');
				$body->appendChild($dom->createTextNode($body_value));
				$newarticles->appendChild($body);
 
				$dom->save($xmlpath);
			
			}
			else{
				#如果文件不存在，则进行文件写入
				//$dom = new DomDocument('1.0','utf-8');
				$dom->formatOutput = true;
				
 
				$page = $dom->createElement('urlset');
				$dom->appendChild($page);
 
				$articles = $dom->createElement('url');
				$page->appendChild($articles);
 
				$title = $dom->createElement('loc');
				$title->appendChild($dom->createTextNode($title_value));
				$newarticles->appendChild($title);
              
				$author = $dom->createElement('lastmod');
				$author->appendChild($dom->createTextNode($author_value));
				$newarticles->appendChild($author);
                
				$body = $dom->createElement('changefreq');
				$body->appendChild($dom->createTextNode($body_value));
				$newarticles->appendChild($body);
 
 
				$dom->save($xmlpath);
			}
		}

}