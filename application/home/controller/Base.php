<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 16:41:06
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-07-24 16:38:19
 */
namespace app\home\controller;
use think\Controller;
use app\home\model\User as UserModel;

class Base extends Controller{
    
	public function _initialize(){
		$user = new UserModel;
		if(!($user->isLogin())){
			$this->redirect('login/index');
		}
	}   
}
