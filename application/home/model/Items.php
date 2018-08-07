<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 11:29:46
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-07-27 14:25:01
 */
namespace app\home\model;
use think\Model;
use think\Db;

class Items extends Model{

	public function itemAdd($data){
		$name = $data['name'];
		$description = $data['description'];
		$url = $data['url'] ?  $data['url'] : '';
		$password = $data['password'] ? $data['password'] : '';
		if($url !== ''){
			if( preg_match('/[^a-zA-Z]/',$url) == 1){
				$this->error = "个性域名只能为字母";
				return false;
			}
		}
		$user = session('heredoc_user');

		$insert_data = [
            'name'   => $name,
            'description'  =>  $description,
            'url'   => $url,
            'password' => $password,
            'uid' => $user['id'],
            'sort' => $data['sort'],
            'addtime'  =>  time()
		];
        $itid = Db::name('items')->insertGetId($insert_data);
		if($itid){
			//插入成员表
			$member_data = [
                'itid'  => $itid,
                'uid'   => $user['id'],
                'username' => $user['username'],
                'readonly' => 0,
                'addtime' => time()
			];
			Db::name('members')->insert($member_data);
			return true;
		}else{
			$this->error = '添加失败';
			return false;
		}
	}
}