<?php
namespace app\home\controller;
use think\Db;
class Index extends Base{
    /**
	 * 项目首页
	 * @return [type] [description]
	 */
	public function index(){

		$user = session('heredoc_user');
		$pic = Db::name('user')->where('id',$user['id'])->value('pic');		
        $items = Db::name('members')->alias('a')->join(config('prefix').'items b','a.itid = b.id')->where('a.uid',$user['id'])->order('b.sort, b.id DESC')->select();
        $this->assign('items',$items);
        $this->assign('pic',$pic);
        return $this->fetch();
	}
}
