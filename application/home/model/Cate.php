<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-23 17:01:27
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-07-23 17:23:16
 */
namespace app\home\model;
use think\Model;
use think\Db;

class Cate extends Model{

	public function cateAdd($data){
		$name = $data['name'];
		$pid = $data['pid'];
		$sort = $data['sort'];
		$itid = $data['itid'];
		$user = session('heredoc_user');
		//文件夹
		$cate_insert = [
            'name' => $name,
            'pid'  => $pid,
            'uid' => $user['id'],
            'itid' => $itid,
            'sort' => $sort,
            'addtime' => time(),
            'is_doc' => 1
		];

		if(self::save($cate_insert)){
			return true;
		}else{
			$this->error = '添加失败';
			return false;
		}		
	}
	public function cateEdit($data){
		$name = $data['name'];
		$pid = $data['pid'];
		$sort = $data['sort'];
		$itid = $data['itid'];
		$id = $data['id'];
		$user = session('heredoc_user');
		//文件夹
		$cate_update = [
            'name' => $name,
            'pid'  => $pid,
            'itid' => $itid,
            'sort' => $sort,
            'addtime' => time(),
            'is_doc' => 1
		];

		if(self::where(['uid'=>$user['id'],'id'=>$id])->update($cate_update)){
			return true;
		}else{
			$this->error = '修改失败';
			return false;
		}		
	}	
}