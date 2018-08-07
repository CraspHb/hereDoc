<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 17:33:53
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-06 17:57:19
 */
namespace app\home\controller;
use think\Request;
use app\home\model\Docs as DocModel;
use think\Db;

class Docs extends Base{
	
	/**
	 * 添加项目
	 * @return [type] [description]
	 */
    public function docsAdd(){
        $user = session('heredoc_user');
    	if(Request::instance()->isPost()){
    		$data = input('post.');
            $this->get_auth($user['id'],$data['itid']);
    		$docs = new DocModel;
    		if($docs->docsAdd($data)){
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 1,
                     'description' => "新建了文档 << ".$data['title']." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);   
				$this->success("新建成功",url('cate/index',['id'=>$data['itid']]));
	    	}else{
               $this->error($docs->getError());
	    	}
    	}else{
            //找出来目录结构
            $id = input('id','');
            $this->get_auth($user['id'],$id);
            $cate = Db::name('cate')->where(['itid'=>$id,'uid'=>$user['id'],'is_doc'=>1])->order('is_doc,sort')->select();
            $tree = new \Tree($cate);
            $cate_tree = $tree->get_tree_list(); 
            $this->assign('cate',$cate_tree);
            $this->assign('id',$id);
    		return $this->fetch();
    	} 
    }
    public function docsEdit(){
        $user = session('heredoc_user');
        if(Request::instance()->isPost()){
            $data = input('post.');
            $this->doc_auth($user['id'],$data['id']);
            $this->get_auth($user['id'],$data['itid']);
            $docs = new DocModel;
            if($docs->docsEdit($data)){
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 2,
                     'description' => "修改了文档 << ".$data['title']." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                  
                $this->success("修改成功",url('cate/index',['id'=>$data['itid'],'page_id'=>$data['id']]));
            }else{
               $this->error($docs->getError());
            }
        }else{
            //找出来目录结构
            $id = input('id','');
            $this->doc_auth($user['id'],$id);
            $docs = Db::name('docs')->where(['id'=>$id,'uid'=>$user['id']])->find();
            $itid = Db::name('cate')->where(['id'=>$docs['cid']])->value('itid');
            $this->get_auth($user['id'],$itid);
            $cate = Db::name('cate')->where(['itid'=>$itid,'uid'=>$user['id'],'is_doc'=>1])->order('is_doc,sort')->select();

            $tree = new \Tree($cate);
            $cate_tree = $tree->get_tree_list(); 
            $this->assign('cate',$cate_tree);
            $this->assign('docs',$docs);
            $this->assign('itid',$itid);
            return $this->fetch();
        }         
    }
    public function docsCopy(){
            //找出来目录结构
            $id = input('id','');
            $user = session('heredoc_user');
            $docs = Db::name('docs')->where(['id'=>$id,'uid'=>$user['id']])->find();
            $itid = Db::name('cate')->where(['id'=>$docs['cid']])->value('itid');
            $this->get_auth($user['id'],$itid);
            $cate = Db::name('cate')->where(['itid'=>$itid,'uid'=>$user['id'],'is_doc'=>1])->order('is_doc,sort')->select();

            $tree = new \Tree($cate);
            $cate_tree = $tree->get_tree_list(); 
            $this->assign('cate',$cate_tree);
            $this->assign('docs',$docs);
            $this->assign('itid',$itid);
            return $this->fetch();        
    }
    /**
     * 存储为模板
     * @return [type] [description]
     */
    public function docTemp(){
        if(Request::instance()->isPost()){
            $user = session('heredoc_user');
            $title = input('title');
            $content = input('template');
            $data = [
                 'uid'  => $user['id'],
                 'title' => $title,
                 'content' => $content,
                 'addtime' => time()
            ];
            if(Db::name('template')->insert($data)){
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 1,
                     'description' => "添加了模板 << ".$title." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                  
                $this->success('模板添加成功');
            }else{
                $this->error('操作失败');
            }
        }
    }
    /**
     * 删除文档
     * @return [type] [description]
     */
    public function docsDel(){
        if(Request::instance()->isPost()){
            $id = input('id');

            $pass = input('pass');
            $user = session('heredoc_user');

            $this->doc_auth($user['id'],$id);

            $userinfo = Db::name('user')->find($user['id']);
            $pwd = get_pwd($pass,$userinfo['pwd_mix']);
            if($userinfo['password'] !== $pwd){
                    $this->error('用户密码不正确');                    
            }
            $doc = Db::name('docs')->where(['id'=>$id])->field('cid,title')->find();
            $cid = $doc['cid'];
            $is_doc = Db::name('cate')->where(['id'=>$cid])->value('is_doc');
            if(!$is_doc){
                Db::name('cate')->where('id',$cid)->delete();
            }
            if(Db::name('docs')->where('id',$id)->delete()){
                $dynamic = [
                     'uid' => $user['id'],
                     'type' => 0,
                     'description' => "删除了文档 << ".$doc['title']." >>",
                     'addtime'   => time(),
                ];    
                Db::name('dynamic')->insert($dynamic);                  
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }
    }   
    public function dataApiTest(){
        if(Request::instance()->isPost()){
             $hostname = trim(input('hostname'));
             $port = trim(input('port'));
             $username = trim(input('username'));
             $password = trim(input('password'));
             $database = trim(input('database'));
             //连接测试
             $dsn = "mysql:host={$hostname};port={$port};dbname={$database};charset=UTF8;";
             try{
                $DB = new \PDO($dsn,$username,$password);
                $this->success('连接成功');
            }catch(\PDOException $e){
                $this->error('连接失败'.$e->getMessage());
            }
        }
    }
    public function dataApi(){
        if(Request::instance()->isPost()){
             $hostname = trim(input('hostname'));
             $port = trim(input('port'));
             $username = trim(input('username'));
             $password = trim(input('password'));
             $database = trim(input('database'));
             //连接测试
             $dsn = "mysql:host={$hostname};port={$port};dbname={$database};charset=UTF8;";
             try{
                $pdo = new \PDO($dsn,$username,$password);
            }catch(\PDOException $e){
                $this->error('连接失败'.$e->getMessage());
            }
            $tables = $pdo->query('SHOW TABLE STATUS')->fetchAll(\PDO::FETCH_ASSOC);
            $data = [];
            // - 用户表，储存用户信息

            // |字段|类型|空|默认|注释|
            // |:----    |:-------    |:--- |-- -|------      |
            // |uid    |int(10)     |否 |  |             |
            // |username |varchar(20) |否 |    |   用户名  |
            // |password |varchar(50) |否   |    |   密码    |
            // |name     |varchar(15) |是   |    |    昵称     |
            // |reg_time |int(11)     |否   | 0  |   注册时间  |

            // - 备注：无
            foreach($tables as $k => $v){
                $data[$k]['name'] = $v['Name']; // 数据库表名称
                $data[$k]['comment'] = $v['Comment']; //数据库注释
                $data[$k]['type'] = $v['Engine'];
                $fields = $pdo->query("show full columns from {$v['Name']}")->fetchAll(\PDO::FETCH_ASSOC);
                foreach($fields as $kk => $vv){
                    $data[$k]['fields'][] = $vv;               
                }
            }

            $markdown  = "";
            foreach($data as $k => $v){
                $markdown .= "\r\n### 表名：{$v['name']}\r\n";
                $markdown .= "- 注释：{$v['comment']}\r\n";
                $markdown .= "- 类型：{$v['type']}\r\n\r\n";
                $markdown .= "|字段|类型|空|默认|注释|\r\n";
                $markdown .= "|:----    |:-------    |:--- |-- -|------      |\r\n";
                foreach($v['fields'] as $kk => $vv){
                    $type = str_replace('unsigned','',$vv['Type']);
                    $null = $vv['Null'] == "NO" ? "否":"是";
                    $markdown .= "|{$vv['Field']}    |{$type}     |{$null} |{$vv['Default']}  | {$vv['Comment']}            |\r\n";
                }
                 $markdown .= " \r\n\r\n - 备注：无\r\n\r\n";
            };
            return $this->success('获取成功','',$markdown);
        }
    }    
    /**
     * 判断操作权限
     * @param  [type] $uid  [description]
     * @param  [type] $itid [description]
     * @return [type]       [description]
     */
    protected function get_auth($uid , $itid){
        //是否加入协作
        $readonly = Db::name('members')->where(['itid'=>$itid,'uid'=>$uid])->value('readonly');
        if($readonly !== 0){
            $this->error('无操作权限');
        }
    } 
    /**
     * 判断修改删除的是否是自己创建的
     * @param  [type] $uid [description]
     * @param  [type] $id  [description]
     * @return [type]      [description]
     */
    protected function doc_auth($uid,$id){
       $doc = Db::name('docs')->where(['id'=>$id,'uid'=>$uid])->find();
       if(empty($doc)){
           $this->error('无操作权限');
       }
    }
    /**
     * editor编辑器上传图片
     * @return [type] [description]
     */
    //  success : 0 | 1,           // 0 表示上传失败，1 表示上传成功
    // message : "提示的信息，上传成功或上传失败及错误信息等。",
    // url     : "图片地址"        // 上传成功时才返回
    public function uploadImg(){
        $img = request()->file('editormd-image-file');
        
        if($img){
            $filePath = ROOT_PATH . 'public' . DS . 'upload' . DS . 'editor';
            if(!is_dir($filePath)){
                mkdir($filePath,0777,true);
            }
            $info = $img->validate(['size'=>15678,'ext'=>'jpg,png,gif,bmp,jpeg,webp'])->move($filePath);
            if($info){
                
                $imgName = $info->getSaveName();
                $domain = Request::instance()->domain();
                $imgPath = $domain . DS . 'upload' . DS . 'editor'. DS . $imgName;
                return json(['success'=>1,'message'=>'上传成功','url'=>$imgPath]);
            }else{
                // 上传失败获取错误信息
                return json(['success'=>0,'message'=>$img->getError(),'url'=>'']);
            }
        }
    }
    /**
     * ajax获取模板
     * @return [type] [description]
     */
    public function ajaxGetTemp(){
       if(Request::instance()->isPost()){
          $user = session('heredoc_user');
          $temp = Db::name('template')->where('uid',$user['id'])->order('id DESC')->select();
           $str = '<div class="layui-form"><table class="layui-table"  lay-skin="line"><colgroup><col width="25%"><col width="55%"><col width="20%"><col></colgroup><thead><tr><th>添加时间</th><th>模板名称</th><th style="text-align:center">操作</th></tr> </thead><tbody>';
          
            foreach($temp as $k => $v){
                 $str .= ' <tr>';
                 $str .= "<td>".date('Y-m-d H:i',$v['addtime'])."</td><td>".$v['title']."</td>";
                 $str .= '<td><a style="display:inline-block;font-size:12px;padding:0px 3px;color:#409eff;cursor:pointer" class="temp-insert" data-id="'.$v['id'].'">插入模板</a><a style="display:inline-block;font-size:12px;padding:0px 3px;color:#409eff;cursor:pointer" class="temp-del"  data-id="'.$v['id'].'">删除模板</a></td></tr> ';
            }                                                     
           $str .=  '</tbody></table></div>';
           return $str;
       }
    }
    /**
     * ajax获取历史版本
     * @return [type] [description]
     */
    public function docsHistory(){
        if(Request::instance()->isPost()){
               $user = session('heredoc_user');
               $id = input('id');
               $history = Db::name('history')->where(['doc_id'=>$id])->order('id DESC')->select();
               $str = '<div class="layui-form"><table class="layui-table"  lay-skin="line"><colgroup><col width="25%"><col width="45%"><col width="30%"><col></colgroup><thead><tr><th>修改时间</th><th>修改人</th><th style="text-align:center">操作</th></tr> </thead><tbody>';
              
                foreach($history as $k => $v){
                     $str .= ' <tr>';
                     $str .= "<td>".date('Y-m-d H:i',$v['addtime'])."</td><td>".$v['username']."</td>";
                     $str .= '<td><a style="display:inline-block;font-size:12px;padding:0px 3px;color:#409eff;cursor:pointer" class="history-diff" href="'.url('Docs/diff',['id'=>$v['id']]).'">版本预览</a><a style="display:inline-block;font-size:12px;padding:0px 3px;color:#409eff;cursor:pointer" class="history-insert"  data-id="'.$v['id'].'">恢复到此版本</a></td></tr> ';
                }                                                     
               $str .=  '</tbody></table></div>';
               return $str;
       }
    }
    public function getHistory(){
        if(Request::instance()->isPost()){
              $id = input('post.id');
              $content = Db::name('history')->where(['id'=>$id])->value('content');
              return htmlspecialchars_decode($content);
        }        
    }
    /**
     * 获取模板内容
     * @return [type] [description]
     */
    public function tempGetContent(){
        if(Request::instance()->isPost()){
              $user = session('heredoc_user');
              $id = input('post.id');
              $content = Db::name('template')->where(['uid'=>$user['id'],'id'=>$id])->value('content');
              return htmlspecialchars_decode($content);
        }
    }
    /**
     * 删除模板
     * @return [type] [description]
     */
    public function tempDel(){
        if(Request::instance()->isPost()){
              $user = session('heredoc_user');
              $id = input('post.id');
              $res = Db::name('template')->where(['uid'=>$user['id'],'id'=>$id])->delete();
              if($res){
                  $this->success('删除成功');
              }else{
                  $this->error('删除失败');
              }
        }        
    }
    public function diff(){
        $id = input('id');
        $history = Db::name('history')->field('doc_id,content')->find($id);
        $base = Db::name('docs')->where('id',$history['doc_id'])->value('content');
        $newtxt = htmlspecialchars_decode($history['content']);
        $this->assign('base',$base);
        $this->assign('newtxt',$newtxt);
        return $this->fetch();
    }
}
