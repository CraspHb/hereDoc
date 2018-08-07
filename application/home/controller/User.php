<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-23 09:31:48
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-03 17:48:40
 */
namespace app\home\controller;
use think\Db;
use think\Request;

class User extends Base{
	public function _initialize(){
		$user = session('heredoc_user');
		$userinfo = Db::name('user')->find($user['id']);
		$members = Db::name('members')->alias('a')->join(config('prefix').'items b','a.itid = b.id')->where(['a.uid'=>$user['id']])->count();
		$my = Db::name('items')->where('uid',$user['id'])->count();
		$this->assign('userinfo',$userinfo);
		$this->assign('members',$members);
		$this->assign('my',$my);
	}
	/**
	 * 个人中心首页
	 * @return [type] [description]
	 */
	public function index(){
		$user = session('heredoc_user');
		$dynamic = Db::name('dynamic')->where('uid',$user['id'])->order('id DESC')->select();
		$this->assign('dynamic',$dynamic);
        return $this->fetch();
	}
	public function myItem(){
		$user = session('heredoc_user');
        $items = Db::name('items')->where('uid',$user['id'])->order('sort')->select();
        $this->assign('items',$items);
        return $this->fetch();
	}	
	public function partItem(){
		$user = session('heredoc_user');
        $items = Db::name('members')->alias('a')->join(config('prefix').'items b','a.itid = b.id')->where(['a.uid'=>$user['id'],'b.uid'=>['neq',$user['id']]])->order('b.sort')->select();
        $this->assign('items',$items);		
        return $this->fetch();
	}
	public function set(){
        return $this->fetch();
	}	
	/**
	 * 修改个人资料
	 * @return [type] [description]
	 */
	public function userSet(){
		if(Request::instance()->isPost()){
			//$data = ['info'=>input('post.info'),'sex'=>input('post.sex'),'tel'=>input('')]
			$data = input('post.');
			$user = session('heredoc_user');
			$res = Db::name('user')->where('id',$user['id'])->data($data)->update();
			if($res){
 				$dynamic = [
                     'uid' => $user['id'],
                     'type' => 2,
                     'description' => "修改了个人资料",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);  				
				$this->success('修改成功');
			}else{
				$this->error('操作失败');
			}
		}
	}
	/**
	 * 修改信息
	 * @return [type] [description]
	 */
	public function pwdSet(){
		if(Request::instance()->isPost()){
			//$data = ['info'=>input('post.info'),'sex'=>input('post.sex'),'tel'=>input('')]
			$pwd = trim(input('pwd'));
			$npwd = trim(input('npwd'));
			$repwd = trim(input('repwd'));

			$user = session('heredoc_user');
			$userinfo = Db::name('user')->find($user['id']);

			$pwd_mix = $userinfo['pwd_mix'];
	        if($userinfo['password'] !== get_pwd($pwd,$pwd_mix)){
				$this->error('密码不正确');                   	
	        }
	        if($npwd != $repwd){
	        	$this->error('两次输入密码不一致');
	        }
			
			$res = Db::name('user')->where('id',$user['id'])->setField('password',get_pwd($npwd,$pwd_mix));
			if($res){
				$dynamic = [
                     'uid' => $user['id'],
                     'type' => 2,
                     'description' => "修改了个人密码",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic); 				
				$this->success('修改成功');
			}else{
				$this->error('操作失败');
			}
		}
	}
	public function setPic(){
        $img = request()->file('file');
        $user = session('heredoc_user');
        if($img){
            $filePath = ROOT_PATH . 'public' . DS . 'upload' . DS . 'face';
            if(!is_dir($filePath)){
                mkdir($filePath,0777,true);
            }
            $info = $img->validate(['size'=>15678,'ext'=>'jpg,png,gif,bmp,jpeg,webp'])->move($filePath);
            if($info){
                
                $imgName = $info->getSaveName();
                $res = Db::name('user')->where('id',$user['id'])->setField('pic',$imgName);
                if($res){
                	$this->success('上传成功');
                }else{
                	$this->error('上传失败');
                }
            }else{
                $this->error('上传失败');
            }
        }
    }	
}