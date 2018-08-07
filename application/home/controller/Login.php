<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-07-19 09:27:34
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-08-02 09:16:53
 */
namespace app\home\controller;
use think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use app\home\model\User;
use think\Request;
use think\captcha\Captcha;

class Login extends Controller{
    public function index(){
    	$userModel = new User;
    	if($userModel->isLogin()){
            $this->redirect('index/index');
    	}
        return $this->fetch();
    }
    /**
     * 注册
     * @return [type] [description]
     */
    public function register(){
    	if(Request::instance()->isPost()){
			$data = input('post.');
	    	$userModel = new User;
	    	if($res = $userModel->userAdd($data)){
				$this->success("注册成功",url('login/index'));
	    	}else{
               $this->error($userModel->getError(),url('login/register'));
	    	}
    	}else{
    		return $this->fetch();
    	}
    	
    }
    /**
     * 登录
     * @return [type] [description]
     */
    public function runLogin(){
        if(Request::instance()->isPost()){
        	$data = input('post.');
	    	$userModel = new User;
	    	if(!($this->check_verify($data['verify']))){
				$this->error('验证码错误',url('login/index'));
	    	}
	    	if($res = $userModel->userLogin($data)){
				$this->success("登录成功",url('index/index'));
	    	}else{
               $this->error($userModel->getError(),url('login/index'));
	    	}
        }
    }
    protected function check_verify($code){
	    $captcha = new Captcha();
	    return $captcha->check($code);
	}
    public function captcha(){
    	$config =    [
		    // 验证码字体大小
		    'fontSize'    =>    17,    
		    // 验证码位数
		    'length'      =>    4,   
		    // 关闭验证码杂点
		    'useNoise'    =>    false, 
		    'imageH'      => 40,
		    'imageW'      => 120,
		    'fontttf'     => '2.ttf'
		];
    	$captcha = new Captcha($config);
		return $captcha->entry();
    }
	public function sendEmail(){
		$email = input('email');
        $mail = new PHPMailer(true);  
        $code = get_code();                       
        try {
            $mail->SMTPDebug = 1;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.qq.com';                          // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'crasphb@qq.com';                 // SMTP username
            $mail->Password = 'pejkycllhsoobdce';                           // SMTP password
            $mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
            $mail->setFrom('crasphb@qq.com', '中教网盟');
            //$mail->addAddress('crasphb@qq.com');
            $mail->addAddress("{$email}");
            $mail->Subject = '欢迎访问hereDoc';
            $mail->Body    = "欢迎访问hereDoc,您的邮箱验证码是：{$code}，有效期为5分钟 ";
            if($mail->send()){
            	cookie('emailCode', md5($code), 300);
            }
        } catch (Exception $e) {
               $this->error('发送失败');
        }
	}  
    public function logout(){
            session('heredoc_user',null);
            cookie('heredoc_user',null);        
            $this->redirect('Login/index');
    }
}
