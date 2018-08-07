<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-25 09:44:06
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-07-30 11:35:04
 */
namespace app\home\controller;
use think\Db;
use think\Request;
use think\Controller;
use app\home\model\User as UserModel;

class Share extends Controller{
	public function index(){
		$id = input('id','');
		$url = input('url','');
		$page_id = input('page_id','');
		if(!$id && !$url){
			 $this->redirect('index/index');
		}
		//判断是否登录
		$user = new UserModel;		
		//使用了个性域名
		if($id == ''){
            $id = Db::name('items')->where('url',$url)->value('id');
		}
		$userinfo = session('heredoc_user');
		$share_cookie = cookie('share-id'.$id.'-'.$page_id);
		$share_cookie_item = cookie('share-id'.$id.'-'.'');
		//没登录
		if(!$share_cookie && !$share_cookie_item){
			if(!$user->isLogin()){
				//判断是否有密码
				$item = Db::name('items')->find($id);
				if(!empty($item['password'])){
	                 $this->redirect('Share/password',['id'=>$id,'page_id'=>$page_id]);
				}
			}
		}
		
		//登录
		if($user->isLogin()){
			$item = Db::name('members')->alias('a')->join(config('prefix').'items b','a.itid = b.id')->where(['a.uid'=>$userinfo['id'],'a.itid'=>$id])->field('b.uid,b.password')->find();

			if(!empty($item) && $item['uid'] == $userinfo['id']){   //是我创建的则不需要输入密码直接跳到分类页
				$this->redirect('cate/index',['id'=>$id,'page_id'=>$page_id]);
			}
			if(!$share_cookie  && !$share_cookie_item){
				if(!empty($item['password'])){
					$this->redirect('Share/password',['id'=>$id,'page_id'=>$page_id]);
				}
		    }
		} 
		$doc = Db::name('docs')->where(['id'=>$page_id])->find();
		//找出来目录结构
		$cate = Db::name('cate')->where(['itid'=>$id])->order('is_doc,sort')->select();
		$cate_tree = get_cate_list($cate);
        
		$ids = get_all_parents($cate , $doc['cid']);		
        
		

		$this->assign('cate',$cate_tree);
		$this->assign('doc',$doc);
		$this->assign('id',$id);
		$this->assign('ids',$ids);
		$this->assign('page_id',$page_id);
		return $this->fetch();
	}
	public function password(){
		$id = input('id','');
		$page_id = input('page_id','');
		$user = new UserModel;	
		$isLogin = $user->isLogin();	
        if(Request::instance()->isPost()){
        	$data = input('post.');
            $item = Db::name('items')->find($id);
            if($item['password'] != $data['password']){
            	$this->error('访问密码不正确');
            }
            cookie('share-id'.$id.'-'.$page_id , 'share-id'.$id.'-'.$page_id,60*5);  //保存5分钟
            $this->success('密码正确',url('Share/index',['id'=>$id,'page_id'=>$page_id]));
        }
        $isLogin = $isLogin ? "1": "0";
        $this->assign('isLogin',$isLogin);
        $this->assign('id',$id);
        $this->assign('page_id',$page_id);
        return $this->fetch();
    }
}