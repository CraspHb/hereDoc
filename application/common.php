<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;
// 应用公共文件
/**
 * 获取邮箱验证码
 * @return [type] [description]
 */
function get_code(){
	$str = "0123456789";
	$code = substr(str_shuffle($str),0,4);
	return $code;
}   
/**
 * 得到登录加密的字符串
 * @return [type] [description]
 */
function get_mix(){
	$str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$mix = substr(str_shuffle($str),0,10);
	return $mix;	
}
/**
 * 获取加密后的密码
 * @param  [type] $pwd [description]
 * @param  [type] $mix [description]
 * @return [type]      [description]
 */
function get_pwd($pwd , $mix){
   return md5(md5($pwd.$mix.'hbb'));
}
/**
 * 获取分类的列表
 * @param  [type]  $arr   [description]
 * @param  integer $id    [description]
 * @param  integer $level [description]
 * @return [type]         [description]
 */
function get_cate_list($arr , $id = 0 , $level = 1){
    $array = [];
    foreach($arr as $k => $v){
    	if( $v['pid'] == $id ){
    		$lev = $level;
    		$v['level'] = $lev;
    		//找找所包含的文档
            if($v['is_doc'] == 0){
                $v['docs'] =  Db::name('docs')->where('cid',$v['id'])->value('id');
            }else{
                $v['docs'] = Db::name('docs')->where('cid',$v['id'])->field('id,title')->order('sort')->select();
            }
    		
    		$v['child'] = get_cate_list($arr , $v['id'] , ++$lev);
    		$array[] = $v;
    	}
    }
    return $array;
}
/**
 * 找出该id的所有父类的id
 * @param  [type]  $arr [description]
 * @param  integer $id  [description]
 * @return [type]       [description]
 */
function get_all_parents( $arr , $id = 0){
   static $array = [];
   foreach($arr as $k => $v){
        if($v['id'] == $id){
            array_unshift($array,$v['id']);
            get_all_parents($arr , $v['pid']);
        }   
   }
   return $array;
}
/**
 * 得到头像
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function get_avatar($url){
     
     if(empty($url)){
         return config('view_replace_str')['__STATIC__']."/heredoc/image/noface.png";
     }else{
         return config('view_replace_str')['__PUBLIC__']."/upload/face/".$url;
     }
}

function output_word($data,$fileName=''){

    if(empty($data)) return '';

    $data = '
        <html xmlns:v="urn:schemas-microsoft-com:vml"
        xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:w="urn:schemas-microsoft-com:office:word"
        xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta http-equiv=Content-Type content="text/html;  charset=utf-8">
        <style type="text/css">
            table  
            {  
                border-collapse: collapse;
                border: none;  
                width: 100%;  
            }  
            td  
            {  
                border: solid #CCC 1px;  
            }  
            .codestyle{
                word-break: break-all;
                background:silver;mso-highlight:silver;
            }
        </style>
        <meta name=ProgId content=Word.Document>
        <meta name=Generator content="Microsoft Word 11">
        <meta name=Originator content="Microsoft Word 11">
        <xml><w:WordDocument><w:View>Print</w:View></xml></head>
        <body>'.$data.'</body></html>';
    
    $filepath = tmpfile();
    $data = str_replace("<thead>\n<tr>","<thead><tr style='background-color: rgb(0, 136, 204); color: rgb(255, 255, 255);'>",$data);
    $data = str_replace("<pre><code>","<table width='100%' class='codestyle'><pre><code>",$data);
    $data = str_replace("</code></pre>","</code></pre></table>",$data);
    $data = str_replace("<li><code>","<li><code style='background: #f6f6f6;color:rgb(221, 17, 68);padding: 3px;border-radius: 3px;'>",$data);
    $len = strlen($data);
    fwrite($filepath, $data);
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename={$fileName}.doc");
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$fileName.'.doc');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $len);
    rewind($filepath);
    echo fread($filepath,$len);
}
