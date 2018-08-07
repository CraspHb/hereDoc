<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 11:29:46
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-07-19 16:39:08
 */
namespace app\home\model;
use think\Model;
use think\Db;

class User extends Model{
    /**
     * 用户登录
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function userLogin($data){

    	$username = trim($data['username']);
    	$password = $data['password'];
        
        //判断用户是否存在
        $user = Db::name('user')->where('username',$username)->whereOr('email',$username)->find();
    	if(!$user){
				$this->error = "用户不存在";
       	  		return false;            
    	}
    	//比较密码
        $pwd_mix = $user['pwd_mix'];
        $pwd = get_pwd($password,$pwd_mix);
        if($user['password'] !== $pwd){
				$this->error = "用户名或密码不正确";
       	  		return false;                    	
        }
        $update_data = [
            'last_login_ip'  => $user['login_ip'],
            'login_ip'       => request()->ip(),
            'last_login_time' => $user['login_time'],
            'login_time'      => time(),
            'times'            => $user['times'] +1 ,

        ];
        //更新登录信息
        if(self::where('id',$user['id'])->update($update_data)){
				$session_data = [
                     'id' => $user['id'],
                     'pwd_mix' => $user['pwd_mix'],
                     'username' => $user['username'],
                     'email' => $user['email'],
                ];     
            session('heredoc_user',$session_data);
            cookie('heredoc_user',$session_data); 	
			//判断是否记住密码，记住密码默认保存7天
	        if(isset($data['remember'])){
                 cookie('heredoc_user',$session_data,3600*24*7); 
	        }
	        return true;             
        }
    }
    /**
     * 新增用户
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
	public function userAdd($data){

       $username = trim($data['username']);
       $password = trim($data['password']);
       $email = trim($data['email']);
       $verify = $data['verify'];
       if(!isset($data['remember'])){
       	  $this->error = "请先同意用户协议";
       	  return false;
       }
       //判断验证码
       if(md5($verify) !== cookie('emailCode')){
       	  $this->error = '验证码不正确';
       	  return false;
       }
       //判断用户名或者邮箱是否存在
       if(self::where('email',$email)->find()){
			$this->error = '邮箱已存在';
       	  	return false;       	  
       }
       if(self::where('username',$username)->find()){
			$this->error = '用户名已存在';
       	  	return false;       	  
       }
       //获取混淆字段
       $pwd_mix = get_mix();
       $data = [
           'username' => $username,
           'password' => get_pwd($password,$pwd_mix),
           'pwd_mix'  => $pwd_mix,
           'email'    => $email,
           'addtime'  => time()
       ];
       $res = self::save($data);
       if(!$res){
       	  $this->error = "注册失败";
       	  return false;
       }

       return true;
	}
	public function isLogin(){
		$user = session('heredoc_user');
		$cookie = cookie('heredoc_user');
		if(!$user && !$cookie){
			return false;
		}
		if($user || $cookie){
			session('heredoc_user',$cookie);
			return true;
		}
	}
}