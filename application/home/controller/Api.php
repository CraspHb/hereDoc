<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-31 16:27:39
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-01 09:14:32
 */
namespace app\home\controller;
use think\Db;
use think\Request;
use think\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
class Api extends Controller{
	public function index(){
		
		return $this->fetch();
	}
	public function apiRun(){
		$data = input('post.');
		$method = strtoupper($data['method']);
		$url = trim($data['url']);
		$params = $this->changeToArray($data['params']);
		$header = $this->changeToArray($data['header']);
		$cookie = $this->changeToArray($data['cookie']);
		
		$query = [];
		if(!empty($params)){
			$query['query'] = $params;
		}
		if(!empty($header)){
			$query['headers'] = $header;
		}		
		if(!empty($cookie)){
			http://fwpt.zjwam.net/api/user/all_class
			$jar = new CookieJar();
			preg_match('/:\/\/(.*)\//iU',$url,$matches);
			$domain = $matches[1];
			$cookieJar = $jar->fromArray($cookie,$domain);
			$query['cookies'] = $cookieJar;
		}		
        try{
	        $client = new Client();
			$res = $client->request($method, $url,$query);
		}catch(\Exception $e){
		    $this->error($e->getMessage());
		}
		$json = json_decode($res->getBody());
		$markdown = $this->getMarkdown(['method'=>$method,'url'=>$url,'params'=>$params,'header'=>$header,'cookie'=>$cookie,'json'=>$json]);
		$this->success('获取成功','',['json'=>$json,'markdown'=>$markdown]);
	}
	protected function changeToArray($data){
		if(empty($data)) return '';
		$query = array_filter(explode(',',trim($data)));
		//dump($query);die;
		$res = [];
		foreach($query as $k => $v){
			$sub = explode(':',$v);
			if(count($sub) != 2){
				$this->error('格式错误');
			}
            $res[trim($sub[0])] = trim($sub[1]);
		}
		return $res;
	}
	protected function getMarkdown($data){

        $str = "欢迎使用HereDoc\r\n\r\n";
        $str .= "**简要描述：**\r\n -\r\n\r\n";
        $str .= "**请求URL：** \r\n- ` {$data['url']} ` \r\n\r\n";
        $str .= "**请求方式：** \r\n- {$data['method']} \r\n\r\n";
        if(!empty($data['header'])){
        	$str .= "**HEADER** \r\n";
        	foreach($data['header'] as $k => $v){
        		$str .= "- {$k} = {$v} \r\n";
        	}
        }
//         **参数：** 

// |参数名|必选|类型|说明|
// |:----    |:---|:----- |-----   |
// |username |是  |string |用户名   |
// |password |是  |string | 密码    |
// |name     |否  |string | 昵称    |
        if(!empty($data['cookie'])){
        	$str .= "**COOKIES** \r\n";
        	foreach($data['cookie'] as $k => $v){
        		$str .= "- {$k} = {$v} \r\n";
        	}
        }
        if(!empty($data['params'])){
        	  $str .= "\r\n**参数：** \r\n\r\n";
        	  $str .= "|参数名|必选|类型|说明|\r\n|:----    |:---|:----- |-----   |\r\n";
        	  foreach($data['params'] as $k => $v){
        	  	  $type = gettype($k) == 'integer' ? 'Int' : ucfirst(gettype($k));
                  $str .= "|{$k}  |必选 |".$type."  |无  |\r\n";
        	  }
        }
        $str .= " \r\n**返回示例** \r\n\r\n";
        $str .= "``` \r\n";
        return $str;
	}
}