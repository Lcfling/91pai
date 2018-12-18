<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台登录控制器
 */
defined('LMXCMS') or exit();
class LoginAction extends Action{
    private $manageModel = null;
	private $code=code;
    public function __construct() {
        parent::__construct();
        if($this->manageModel == null){
            $this->manageModel = new ManageModel();
        }
    }
    
    //登录视图
    public function index(){
        self::check_ip(); //验证ip
        if(self::isLogin()){
            rewrite::succ('您已经登录',u('Index','index'));
        }
		
        $this->smarty->display('Login/index.html');
    }
    
    //登录表单提交验证
    public function login(){
        self::check_ip(); //验证ip
        if(!isset($_POST['sub'])){
            rewrite::js_back('禁止非法提交');
        }
        //获取登录数据
        $data = p(1,1,1);
        if(empty($data['name']) || empty($data['pwd'])){
            rewrite::js_back('帐号或密码不能为空');
        }
        //获取用户数据
        $userData = $this->manageModel->getNameUserData($data['name']);
        if(!$userData) rewrite::js_back('用户名或者密码有误');
        //修改登录错误次数为0
        if($userData['num'] >= $this->config['login_num'] && time() - $userData['errortime'] > ($this->config['login_out_time'] * 60)){
            $this->manageModel->removeErrorNum($userData['name']);
        }
        //判断登录错误次数限制
        if($userData['num'] >= $this->config['login_num'] && time() - $userData['errortime'] < ($this->config['login_out_time'] * 60)){
            rewrite::error('该用户密码错误次数过多，请稍后在尝试登录！','',3000);
        }
        $ischeck = $this->manageModel->LoginData($userData,$data);
        if(!$ischeck) rewrite::js_back ('用户名或者密码有误');
        //保存日志
        addlog('【'.$data['name'].'】登录后台');
        rewrite::succ('登录成功',u('Index','index'));
    }
    
    //验证ip
    private static function check_ip(){
        if($GLOBALS['public']['is_ip']){
            $ip = getip(); //取得ip
            if(!$ip) exit;
            //格式化数据
            $ip_list = explode("\n",$GLOBALS['public']['ip_list']);
            $ip_not = 1;
            if($ip_list){
                foreach($ip_list as $v){
                    $v = trim($v);
                    if(!$v) continue;
                    $zz = str_replace('.','\.',$v);
                    $zz = str_replace('*','[0-9]{1,3}',$zz);
                    if(preg_match("/^($zz)$/",$ip)){
                        $ip_not = 0;
                        break;
                    }
                }
            }
            if($ip_not == 1) exit('您的ip不在系统的白名单中，拒绝您登录后台！');
        }
    }
    
    //验证登录是否有效并获取管理员名字
    public static function isLogin(){
        //验证登录次数限制
        self::check_ip();
        $loginInfo['adminname'] = session('adminname');
        $loginInfo['adminpwd'] = session('adminpwd');
        $loginInfo['admintime'] = session('admintime');
        $loginInfo['adminKey'] = session('adminKey');
//        $loginInfo['login_mark'] = $_COOKIE['login_mark'];
        foreach($loginInfo as $v){
            if(!$v){
                self::unsession();
                return false;
            }
        }
//        //不允许同一个帐号同时登录
//        if(encrypt($loginInfo['login_mark'],'D',$GLOBALS['public']['user_pwd_key']) != $GLOBALS['public']['login_mark']){
//            self::unsession();
//            rewrite::error('您的帐号已在其他地点登录',u('Login','index'));
//        }
        global $config;
        //判断超时登录
        if(time() - $loginInfo['admintime'] > $config['user_out_time'] * 60){
            self::unsession();
            rewrite::error('登录超时，请重新登录',u('Login','index'));
        }else{
            session('admintime',time());//更新超时时间
        }
        //判断帐号与密码是否正确
        if(string::pwdmd5($loginInfo['adminname'].$loginInfo['adminpwd']) != $loginInfo['adminKey']){
            self::unsession();
            return false;
        }
        return true;
    }
    
    //判断如果没有登录转向到登录页面
    public static function isloginAction(){
        if(!self::isLogin()){
            rewrite::error('您还没有登录',u('Login','index'));
        }
        return encrypt(session('adminname'),'D',$GLOBALS['public']['user_pwd_key']);
    }
    
    //注销登录
    public function logout(){
        addlog('【'.encrypt(session('adminname'),'D',$GLOBALS['public']['user_pwd_key']).'】退出后台');
        self::unsession();
        //跳转后台登录页面
        rewrite::succ('退出登录成功',u('Login','index'));
    }
    
    //注销session
    public static function unsession(){
        unseion('adminpwd');
        unseion('adminKey');
        unseion('adminname');
        unseion('admintime');
//        setcookie('login_mark','',time() - 3600);
    }
    public function Code(){
	    $code=new secoder;
		$code->entry();
	}
}
?>