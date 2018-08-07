<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 17:33:53
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-04 10:08:45
 */
namespace app\home\controller;
use think\Request;
use app\home\model\Items;
use think\Db;

class Item extends Base{
	
	/**
	 * 添加项目
	 * @return [type] [description]
	 */
    public function itemAdd(){
    	if(Request::instance()->isPost()){
    		$data = input('post.');
    		$item = new Items;
    		if($item->itemAdd($data)){
                $user = session('heredoc_user');
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 1,
                     'description' => "新建了项目<< ".$data['name']." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                   
				$this->success("新建成功",url('Index/index'));
	    	}else{
               $this->error($item->getError());
	    	}
    	}else{
    		return $this->fetch();
    	} 
    }
    public function itemEdit(){
        if(Request::instance()->isPost()){
            $data = input('post.');
            if(Db::name('items')->update($data)){
                $user = session('heredoc_user');
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 2,
                     'description' => "修改了项目<< ".$data['name']." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);   
                $this->success("修改成功");
            }else{
               $this->error("修改失败");
            }
        }
    }    
    public function set(){
        $id = input('id');
        $user = session('heredoc_user');
        $item = Db::name('items')->where(['id'=>$id,'uid'=>$user['id']])->find();
        if(!$item){
             $this->error('无权限修改');
        }
        //成员
        $members = Db::name('members')->where(['itid'=>$id])->select();
        $this->assign([
            'item' => $item,
            'members' => $members
        ]);
        return $this->fetch();
    }
    public function memberAdd(){
        if(Request::instance()->isPost()){
            $id = input('post.id','');
            $username = input('post.username','');
            $readonly = input('post.readonly',0);
            $read = $readonly == '0' ?'0':'1';
            

            $userinfo = Db::name('user')->where('username',$username)->whereOr('email',$username)->find();
            if(!$userinfo){
                $this->error('用户不存在');
            }
            $user = session('heredoc_user');

            if($username == $user['username'] || $username == $user['email']){
                $this->error('不能添加项目创建者为成员');
            }
            if(Db::name('members')->where(['itid'=>$id,'uid'=>$userinfo['id']])->find()){
                $this->error('该成员已经添加过了,请不要重复添加');
            }
            $data = [
                   'itid'  => $id,
                   'uid'   => $userinfo['id'],
                   'username' => $userinfo['username'],
                   'addtime'  => time(),
                   'readonly'  => $read,
            ];
            $item = Db::name('items')->where('id',$id)->value('name');
            if(Db::name('members')->data($data)->insert()){
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 1,
                     'description' => "<< ".$item['name']." >>项目添加了新成员 ".$username,
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                 
                $this->success("新建成功",url('Item/set',['id'=>$id]));
            }else{
               $this->error('添加失败');
            }
        }        
    }  
    public function itemDel(){
        if(Request::instance()->isPost()){
            $id = input('id');
            $user = session('heredoc_user');

            $pass = input('pass');
            $userinfo = Db::name('user')->find($user['id']);
            $pwd = get_pwd($pass,$userinfo['pwd_mix']);
            if($userinfo['password'] !== $pwd){
                    $this->error('用户密码不正确');                    
            }

            $uid = Db::name('items')->where('id',$id)->value('uid');
            if($uid != $user['id']){
                 $this->error('没有权限');
            }
            if(Db::name('items')->where('id',$id)->delete()){
                 //删除分类
                 $cids = Db::name('cate')->where('itid',$id)->column('id');
                 Db::name('cate')->where('itid', $id)->delete();
                 Db::name('docs')->where('cid','in',$cids)->delete();
                 Db::name('members')->where('id',$id)->delete();
                 $item = Db::name('items')->where('id',$id)->value('name');
                 $dynamic = [
                     'uid' => $user['id'],
                     'type' => 0,
                     'description' => "删除了项目 << ".$item['name']." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                     
                 $this->success('删除成功',url('Index/index'));
            }else{
                $this->error('操作失败');
            }
        }
    }  
    /**
     * ajax删除成员
     * @return [type] [description]
     */
    public function membersDel(){
        if(Request::instance()->isPost()){
            $id = input('id');
            $pass = input('pass');
            $user = session('heredoc_user');
            $userinfo = Db::name('user')->find($user['id']);
            $pwd = get_pwd($pass,$userinfo['pwd_mix']);
            if($userinfo['password'] !== $pwd){
                    $this->error('用户密码不正确');                    
            }
            if(Db::name('members')->where('id',$id)->delete()){
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 0,
                     'description' => "删除了项目成员",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                        
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }
    }    
   
}
