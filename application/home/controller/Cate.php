<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-23 09:31:48
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-06 14:58:26
 */
namespace app\home\controller;
use think\Db;
use think\Request;
use app\home\model\Cate as CateModel;
class Cate extends Base{
    /**
	 * 项目首页
	 * @return [type] [description]
	 */
	public function index(){
		$id = input('id','');
		$url = input('url','');
		$page_id = input('page_id','');
		if(!$id && !$url){
			 $this->redirect('index/index');
		}
		//使用了个性域名
		if($id == ''){
            $id = Db::name('items')->where('url',$url)->value('id');
		}
        $user = session('heredoc_user');
        $member = Db::name('members')->where(['itid'=>$id,'uid'=>$user['id']])->find();
        if(empty($member)){
        	$this->error('无操作权限');
        }
        $doc = Db::name('docs')->where(['id'=>$page_id])->find();
		//找出来目录结构
		
		$cate = Db::name('cate')->where(['itid'=>$id])->order('is_doc,sort')->select();
		$cate_tree = get_cate_list($cate);
        
		$ids = get_all_parents($cate , $doc['cid']);
        $readonly = Db::name('members')->where(['uid'=>$user['id'],'itid'=>$id])->value('readonly');

		$docs_auth  = 1;
		if($page_id != ''){
			$docs = Db::name('docs')->where(['id'=>$page_id,'uid'=>$user['id']])->find();
	      	if(empty($docs)){
	           $docs_auth  = 0;
	       	}    
		}
		//dump($cate_tree);die;
		$this->assign('cate',$cate_tree);
		$this->assign('doc',$doc);
		$this->assign('id',$id);
		$this->assign('ids',$ids);
		$this->assign('docs_auth',$docs_auth);
		$this->assign('readonly',$readonly);
		$this->assign('page_id',$page_id);
		return $this->fetch();
	}
	public function docs(){
		$id = input('id','');
		$user = session('heredoc_user');
		$this->get_item_auth($user['id'],$id);

		$cate = Db::name('cate')->where(['itid'=>$id,'uid'=>$user['id'],'is_doc'=>1])->order('is_doc,sort')->select();
		$tree = new \Tree($cate);
		$cate_tree = $tree->get_tree_list(); 
		$this->assign('cate',$cate_tree);
		$this->assign('id',$id);
		return $this->fetch();		      
	}	
	public function cateAdd(){
		if(Request::instance()->isPost()){
    		$data = input('post.');
    		$user = session('heredoc_user');
    		$this->get_item_auth($user['id'],$data['itid']);
    		$cate = new CateModel;
    		if($cate->cateAdd($data)){
    			$dynamic = [
                     'uid' => $user['id'],
                     'type' => 1,
                     'description' => '创建了一个分类',
                     'addtime'   => time(),
    			];
    			Db::name('dynamic')->insert($dynamic);
				$this->success("新建成功",url('Cate/docs',['id'=>$data['itid']]));
	    	}else{
               $this->error($cate->getError());
	    	}
    	}		 
	}
	public function cateEdit(){
		if(Request::instance()->isPost()){
    		$data = input('post.');
    		$user = session('heredoc_user');
    		$this->get_auth($user['id'],$data['itid']);
    		$cate = new CateModel;
    		if($cate->cateEdit($data)){
				$dynamic = [
                     'uid' => $user['id'],
                     'type' => 2,
                     'description' => '修改了一个分类',
                     'addtime'   => time(),
    			];
    			Db::name('dynamic')->insert($dynamic);    			
				$this->success("修改成功",url('Cate/docs',['id'=>$data['itid']]));
	    	}else{
               $this->error($cate->getError());
	    	}
    	}		 
	}
	/**
	 * ajax删除文件夹
	 * @return [type] [description]
	 */
	public function del(){
        if(Request::instance()->isPost()){
        	$id = input('id');
        	$pass = input('pass');
        	$user = session('heredoc_user');
        	$userinfo = Db::name('user')->find($user['id']);
            $pwd = get_pwd($pass,$userinfo['pwd_mix']);
            if($userinfo['password'] !== $pwd){
                    $this->error('用户密码不正确');                    
            }
			$cate = Db::name('cate')->where(['id'=>$id,'uid'=>$user['id']])->find();

			
    		$this->get_auth($user['id'],$cate['itid']);	

			$cate_all = Db::name('cate')->where(['itid'=>$cate['itid'],'uid'=>$user['id']])->select();
		    $tree = new \Tree($cate_all);        	
        	$ids = $tree->get_childs_all($id,true);
        	array_unshift($ids,$id);
        	if(Db::name('cate')->where('id','in',$id)->delete()){
        		//删除对应的文档
        		Db::name('docs')->where('cid','in',$id)->delete();
				$dynamic = [
                     'uid' => $user['id'],
                     'type' => 0,
                     'description' => '删除了一个文件夹',
                     'addtime'   => time(),
    			];    
    			Db::name('dynamic')->insert($dynamic);     		
        	    $this->success('删除成功');
        	}else{
        		$this->error('删除失败');
        	}
        }
	}
	/**
	 * 判断文件夹操作权限
	 * @param  [type] $uid  [description]
	 * @param  [type] $itid [description]
	 * @return [type]       [description]
	 */
	protected function get_auth($uid , $itid){
        $cate = Db::name('cate')->where('itid',$itid)->value('uid');
		if($uid != $cate){
			$this->error('无操作权限');
		}
	}
	protected function get_item_auth($uid , $itid){
		$item = Db::name('members')->where(['uid'=>$uid,'itid'=>$itid])->find();
		if(!$item || $item['readonly'] != 0){
			$this->error('无操作权限');
		}
	}
	 /**
     * 导出文件夹
     * 目前只有一级目录，所以不考虑多级目录的情况
     * @return [type] [description]
     */
	public function exportCate(){
        $itid = input('itid');
        $cid = input('cid');
        $Parsedown = new \Parsedown();
        $items = Db::name('items')->where('id',$itid)->field('name,description')->find();
        $data = '<h1 style="text-align:center;color:#1E9FFF">'.$items['name'].'</h1><div style="font-size:14px;font-weight:bold;float:right;margin-left:280px;color:#01AAED">————'.$items['description'].'</div>';
        if($cid == 0){ //导出全部
            $cates = Db::name('cate')->where('itid',$itid)->order('sort')->select();
            foreach($cates as $k => $v){
            	$cnum = $k + 1;
            	$docs = Db::name('docs')->where('cid',$v['id'])->order('sort')->select();
				$data .= '<h2>'.$cnum.'、'.$v['name'].'</h2>';
				foreach($docs as $kk => $vv){
					$dnum = $kk + 1 ;
					$data .= '<h3 style="margin-left:20px;">'.$cnum.'.'.$dnum.'、'.$vv['title'].'</h3>';
					$data .= htmlspecialchars_decode($Parsedown->text($vv['content'])); 
				}                	
            }
        }else{
           //项目的名称
            
            $cate = Db::name('cate')->where('id',$cid)->find();
            $docs = Db::name('docs')->where('cid',$cid)->order('sort')->select();
            $data .= '<h2>'.$cate['name'].'</h2>';
			foreach($docs as $k => $v){
				$data .= '<h3 style="margin-left:20px;">'.++$k.'、'.$v['title'].'</h3>';
				$data .= htmlspecialchars_decode($Parsedown->text($v['content'])); 
			}          
        }
        $filename = $items['name'];
        output_word($data,$filename); 
	}
	public function exportDoc(){
		$itid = input('id');
		$id = input('page_id');
		$Parsedown = new \Parsedown();
		$docs = Db::name('docs')->where('id',$id)->find();
		$data = '<h2 style="text-align:center;">'.$docs['title'].'</h2>';
		$data .= htmlspecialchars_decode($Parsedown->text($docs['content'])); 
		$filename = $docs['title'];
        output_word($data,$filename); 
	}
	/**
	 * ajax获取分类
	 * @return [type] [description]
	 */
	public function ajaxGetCate(){
        if(Request::instance()->isPost()){
        	$itid = input('itid','');
	        $user = session('heredoc_user');
			$cate = Db::name('cate')->where(['itid'=>$itid,'uid'=>$user['id'],'is_doc'=>1])->order('is_doc,sort')->select();
			$tree = new \Tree($cate);
			$cate_tree = $tree->get_tree_list(); 
            $id = input('id','');
           if($id == ''){
           	   $html = '
           	   <div class="add">
   	   <div class="form-container">
   	   	    <form class="layui-form  ajaxForm3" action="'.url("Cate/cateAdd").'" method="post">
   	   	    <input type="hidden" name="itid" value="'.$itid.'" />
		   	  <div class="layui-form-item">
			    <label class="lable-title">目录名：</label>
			      <input type="text" name="name" required  lay-verify="required" placeholder="请输入目录名" autocomplete="off" class="layui-input">
			  </div>
				  <div class="layui-form-item">
				    <label class="lable-title">上级目录：</label>
				      <select name="pid" lay-verify="required">
				         <option value="0">&nbsp;&nbsp;&nbsp;</option>
				      ';
				 foreach($cate_tree as $k => $v){
                     $html .= '<option value="'.$v['id'].'">'.str_repeat('----',$v['level']).$v['name'].'</option>';
				 }
				       
				 $html .= '</select>
				  </div> 
				<div class="layui-form-item">
			    <label class="lable-title">排序：</label>
			      <input type="text" name="sort" required  lay-verify="required" value="10" autocomplete="off" class="layui-input">
			  </div> 
			    <div class="layui-form-item">
				    <div class="">
				      <button class="layui-btn" lay-submit lay-filter="formDemo">立即提交</button>
				      <button type="reset" class="layui-btn layui-btn-primary">重置</button>
				    </div>
				  </div>
	   		</form>
   	   </div>
   </div>';
           }else{
           	    $cate = Db::name('cate')->find($id);
				$html = '
			           	   <div class="add">
			   	   <div class="form-container">
			   	   	    <form class="layui-form  ajaxForm3" action="'.url("Cate/cateEdit").'" method="post">
					   	  <div class="layui-form-item">
					   	   <input type="hidden" name="id" value="'.$id.'" />
					   	   <input type="hidden" name="itid" value="'.$itid.'" />
						    <label class="lable-title">目录名：</label>
						      <input type="text" name="name" required  lay-verify="required" value="'.$cate['name'].'" placeholder="请输入目录名" autocomplete="off" class="layui-input">
						  </div>
							  <div class="layui-form-item">
							    <label class="lable-title">上级目录：</label>
							      <select name="pid" lay-verify="required">
							         <option value="0">&nbsp;&nbsp;&nbsp;</option>
							      ';
							 foreach($cate_tree as $k => $v){
							 	if($v['id'] == $cate['pid']){
			                       $html .= '<option selected value="'.$v['id'].'">'.str_repeat('----',$v['level']).$v['name'].'</option>';
							 	}else{
							 		 $html .= '<option value="'.$v['id'].'">'.str_repeat('----',$v['level']).$v['name'].'</option>';
							 	}
							 }
							       
							 $html .= '</select>
							  </div> 
							<div class="layui-form-item">
						    <label class="lable-title">排序：</label>
						      <input type="text" name="sort" required  lay-verify="required"  value="'.$cate['sort'].'"  autocomplete="off" class="layui-input">
						  </div> 
						    <div class="layui-form-item">
							    <div class="">
							      <button class="layui-btn" lay-submit lay-filter="formDemo">立即提交</button>
							      <button type="reset" class="layui-btn layui-btn-primary">重置</button>
							    </div>
							  </div>
				   		</form>
			   	   </div>
			   </div>';           	  
           }
           return $html;
        }
	}
	public function ajaxGetExportCate(){
			$id = input('id','');
	        $user = session('heredoc_user');
			$cate = Db::name('cate')->where(['itid'=>$id,'uid'=>$user['id'],'is_doc'=>1])->order('is_doc,sort')->select();
			$tree = new \Tree($cate);
			$cate_tree = $tree->get_tree_list(); 
			$html = '<div class="add">
                       <div class="form-container" style="margin:15px;">
                            <form class="layui-form" action="'.url("Cate/exportCate").'" method="post">
                            <input type="hidden" name="itid" value="'.$id.'" />
                          <div class="layui-form-item">
                            <label class="lable-title" style="margin:10px 0px;display:inline-block">选择导出的目录：</label>
                              <select name="cid" lay-verify="required">
                                 <option value="0">全部</option>';
		    foreach($cate_tree as $k => $v){
		    	$html .= '<option value="'.$v['id'].'">'.str_repeat('----',$v['level']).$v['name'].'</option>';
		    }                                 
            $html .= ' </select>
                          </div> 
                          <div class="layui-form-item">
                              <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="formDemo">开始导出</button>
                          </div>
                           <div class="layui-form-item">
                          <button type="reset" class="layui-btn layui-btn-primary close layui-btn-fluid">取消</button>
                          </div>
                        </form>
                       </div>
                   </div>';
            return $html;
	}
}
