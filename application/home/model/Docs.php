<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 11:29:46
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-04 16:05:58
 */
namespace app\home\model;
use think\Model;
use think\Db;

class Docs extends Model{

	public function docsAdd($data){
		$title = $data['title'];
		$cid = $data['cid'];
		$sort = $data['sort'];
		$content = $data['content'];
		$user = session('heredoc_user');
		if(!$title){
			$this->error = '标题不能为空';
			return false;			
		}
		//单页
		if($cid == 0){
			$cate_insert = [
                'name' => $title,
                'pid'  => 0,
                'uid' => $user['id'],
                'itid' => $data['itid'],
                'sort' => $sort,
                'addtime' => time(),
                'is_doc' => 0
			];
			$cid = Db::name('cate')->insertGetId($cate_insert);
		}
		$insert_data = [
             'title'  => $title,
             'content'  => $content,
             'cid'  => $cid,
             'uid'  => $user['id'],
             'sort' => $sort,
             'addtime'  => time(),

		];
		if(self::save($insert_data)){
			return true;
		}else{
			$this->error = '添加失败';
			return false;
		}		
	}

	public function docsEdit($data){
		$title = $data['title'];
		$cid = $data['cid'];
		$sort = $data['sort'];
		$content = $data['content'];
		$id = $data['id'];
		$user = session('heredoc_user');
        if(!$title){
			$this->error = '标题不能为空';
			return false;			
		}
		$docs = Db::name('docs')->where(['id'=>$id])->find();
        $is_doc = Db::name('cate')->where(['id'=>$docs['cid']])->value('is_doc');		
		 //单页
		if($cid == 0 && $is_doc != 0){   //目录变单页下
			$cate_insert = [
                'name' => $title,
                'pid'  => 0,
                'uid' => $user['id'],
                'itid' => $data['itid'],
                'sort' => $sort,
                'addtime' => time(),
                'is_doc' => 0
			];
			$cid = Db::name('cate')->insertGetId($cate_insert);
			$is_doc = 0;
		}elseif($cid != 0 && $is_doc == 0){
			Db::name('cate')->where('id',$docs['cid'])->delete();
			$is_doc = 1;			
		}
		$insert_data = [
             'title'  => $title,
             'content'  => $content,
             'cid'  => $cid,
             'sort' => $sort,
		];
		if(self::where(['id'=>$id])->data($insert_data)->update()){
			//插入版本表里
			$history_user = Db::name('user')->where('id',$docs['uid'])->value('username');
			$history = [
                'doc_id' => $id,
                'username' => $history_user,
                'content' => $docs['content'],
                'addtime' => time(),
			];
			Db::name('history')->insert($history);
			return true;
		}else{
			$this->error = '修改失败';
			return false;
		}		
	}	
}