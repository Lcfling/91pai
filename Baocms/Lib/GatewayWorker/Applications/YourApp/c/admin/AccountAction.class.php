<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   用户管理控制器
 */
defined('LMXCMS') or exit();
class AccountAction extends AdminAction{
    private $userModel=null;
    public function __construct() {
        parent::__construct();
        if($this->userModel == null){
            $this->userModel = new UserModel();
        }
    }
    public function index(){
        $data = $this->userModel->getAllData();
        $this->smarty->assign('userData',$data);
        $this->smarty->display('Account/index.html');
    }
    public function association(){
        $data = $this->InfolistVar_for_teacher();
        $this->smarty->display('Account/association.html');
    }
    //获取内容列表视图数据并注入变量
    private function InfolistVar(){
        //获取总条数
        $num = $this->userModel->getUserCount();
        //获取分页
        $page = new page($num,$this->config['page_list_num']);
        //获取信息列表
        $listInfo = $this->userModel->getUserlist($page->returnLimit());
        //赋值url
        if($listInfo){
            foreach($listInfo as $v){
                $param['type'] = 'content';
                $param['classid'] = $v['classid'];
                $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                $param['time'] = $v['time'];
                $param['id'] = $v['id'];
                $v['url'] = $v['url'] ? $v['url'] : url($param);
                $newlist[] = $v;
            }
        }
        $this->smarty->assign('num',$num);
        $this->smarty->assign('userData',$newlist);
        $this->smarty->assign('page',$page->html());
    }
    private function InfolistVar_for_teacher(){
        //获取总条数
        $num = $this->userModel->getUserCount(array("where"=>"vip=1"));
        //获取分页
        $page = new page($num,$this->config['page_list_num']);
        //获取信息列表
        $listInfo = $this->userModel->getUserlist($page->returnLimit(),1);
        //赋值url
        if($listInfo){
            foreach($listInfo as $v){
                $param['type'] = 'content';
                $param['classid'] = $v['classid'];
                $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                $param['time'] = $v['time'];
                $param['id'] = $v['id'];
                $v['url'] = $v['url'] ? $v['url'] : url($param);
                $newlist[] = $v;
            }
        }
        $this->smarty->assign('num',$num);
        $this->smarty->assign('userData',$newlist);
        $this->smarty->assign('page',$page->html());
    }


    //增加用户
    public function add(){
        if(isset($_POST['adduser'])){
            //验证数据
            $data = p(1,1,1);
            $data = $this->checkAccountData($data);
            //验证用户是否存在
            if($this->userModel->isName($data['name'])) rewrite::js_back ('该用户已存在');
            if($this->userModel->addUser($data)){
                addlog('增加账户【'.$data['name'].'】');
                rewrite::succ('增加账户成功');
            }else{
                rewrite::error('增加账户失败，请重试');
            }
            
        }
        $this->smarty->display('Account/addAccount.html');
    }
    
    //修改用户
    public function update(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(empty($id)) rewrite::js_back('参数有误');
        $userData = $this->userModel->getIdUserData($id);
        if(!$userData) rewrite::js_back('该用户不存在');
        if(isset($_POST['updateUser'])){
            $data = p();
            $data = $this->checkAccountData($data);
            if($this->userModel->updateUser($data)){
                addlog('修改账户【'.$data['name'].'】');
                rewrite::succ('修改账户成功','?m=Account');
            }else{
                rewrite::error('修改账户失败，请重试');
            }
        }
        $this->smarty->assign('userdata',$userData);
        $this->smarty->display('Account/updateAccount.html');
    }
    
    //验证数据
    public function checkAccountData($data){
        if(empty($data['name'])) rewrite::js_back('用户名不能为空');
        rewrite::regular_back('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',$data['name'],'用户名格式错误，用户名必须由数字、字母、下划线组成');
        if(empty($data['pwd'])) rewrite::js_back('密码不能为空');
        if(empty($data['pwd2'])) rewrite::js_back('确认密码不能为空');
        if($data['pwd'] != $data['pwd2'])  rewrite::js_back('两次输入的密码不一致');
        return $data;
    }
    
    //删除用户
    public function del(){
        $id = (int)$_GET['id'];
        if(empty($id)) rewrite::js_back('参数有误');
        $data = $this->userModel->getIdUserData($id);
        if(!$data) rewrite::error('该用户不存在');
        if($this->userModel->delManage($id)){
            addlog('删除用户【'.$data['name'].'】');
            if($data['name'] == encrypt(session('username'),'D',$GLOBALS['public']['user_pwd_key'])){
                LoginAction::unsession();
                rewrite::succ('删除用户成功，请用其他帐号登录','?m=Login');
            }
            rewrite::succ('删除成功');
        }else{
            rewrite::error('删除失败，请重试');
        }
    }
    public function levelup(){
        $id=(int)$_GET['id'];
        if(empty($id)) rewrite::js_back('参数有误');
        $state = $this->userModel->levelUpvid($id);
        if(!$state) {
            rewrite::error('升级失败！');
        }else{
            rewrite::succ('升级成功！');
        }
    }
    public function getUser(){
        if(isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $userdata = $this->userModel->getIdUserData($id);
            echo json_encode($userdata);
        }
    }
    
    //登录日志列表
    public function log(){
        $count = $this->userModel->getUserLogCount();
        $page = new page($count,$this->config['page_list_num']);
        $logData = $this->userModel->getUserLog($page->returnLimit());
        $this->smarty->assign('userLog',$logData);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->display('Manage/log.html');
    }
    
    //删除日志
    public function deleteLog(){
        $this->userModel->deleteLog();
        rewrite::succ('删除成功');
    }
}
?>