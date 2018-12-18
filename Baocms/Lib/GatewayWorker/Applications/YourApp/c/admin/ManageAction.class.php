<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   管理员管理控制器
 */
defined('LMXCMS') or exit();
class ManageAction extends AdminAction{
    private $manageModel=null;
    public function __construct() {
        parent::__construct();
        if($this->manageModel == null){
            $this->manageModel = new ManageModel();
        }
    }
    public function index(){
        $data = $this->manageModel->getAllData();
        $this->smarty->assign('userData',$data);
        $this->smarty->display('Manage/manage.html');
    }
    
    
    //增加管理员
    public function add(){
        if(isset($_POST['addManage'])){
            //验证数据
            $data = p(1,1,1);
            $data = $this->checkManageData($data);
            //验证用户是否存在
            if($this->manageModel->isName($data['name'])) rewrite::js_back ('该用户已存在');
            if($this->manageModel->addManang($data)){
                addlog('增加管理员【'.$data['name'].'】');
                rewrite::succ('增加管理员成功');
            }else{
                rewrite::error('增加管理员失败，请重试');
            }
            
        }
        $this->smarty->display('Manage/addManage.html');
    }
    
    //修改管理员
    public function update(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(empty($id)) rewrite::js_back('参数有误');
        $userData = $this->manageModel->getIdUserData($id);
        if(!$userData) rewrite::js_back('该用户不存在');
        if(isset($_POST['updateManage'])){
            $data = p();
            $data = $this->checkManageData($data);
            if($this->manageModel->updateUser($data)){
                //如果修改的是当前管理员，踢出登录
                addlog('修改管理员【'.$data['name'].'】');
                if($userData['name'] == encrypt(session('username'),'D',$GLOBALS['public']['user_pwd_key'])){
                    LoginAction::unsession();
                    rewrite::succ('修改管理员成功，请重新登录','?m=Login');
                }
                rewrite::succ('修改管理员成功','?m=manage');
            }else{
                rewrite::error('修改管理员失败，请重试');
            }
        }
        $this->smarty->assign('userdata',$userData);
        $this->smarty->display('Manage/updateManage.html');
    }
    
    //验证数据
    public function checkManageData($data){
        if(empty($data['name'])) rewrite::js_back('用户名不能为空');
        rewrite::regular_back('/^[\w]+$/',$data['name'],'用户名格式错误，用户名必须由数字、字母、下划线组成');
        if(empty($data['pwd'])) rewrite::js_back('密码不能为空');
        if(empty($data['pwd2'])) rewrite::js_back('确认密码不能为空');
        if($data['pwd'] != $data['pwd2'])  rewrite::js_back('两次输入的密码不一致');
        return $data;
    }
    
    //删除管理员
    public function del(){
        $id = (int)$_GET['id'];
        if(empty($id)) rewrite::js_back('参数有误');
        if($this->manageModel->getUserCount() <= 1){
            rewrite::error('系统只有一个管理员，避免误删，禁止删除');
        }
        $data = $this->manageModel->getIdUserData($id);
        if(!$data) rewrite::error('该用户不存在');
        if($this->manageModel->delManage($id)){
            addlog('删除管理员【'.$data['name'].'】');
            if($data['name'] == encrypt(session('username'),'D',$GLOBALS['public']['user_pwd_key'])){
                LoginAction::unsession();
                rewrite::succ('删除管理员成功，请用其他帐号登录','?m=Login');
            }
            rewrite::succ('删除成功');
        }else{
            rewrite::error('删除失败，请重试');
        }
    }
    
    //登录日志列表
    public function log(){
        $count = $this->manageModel->getUserLogCount();
        $page = new page($count,$this->config['page_list_num']);
        $logData = $this->manageModel->getUserLog($page->returnLimit());
        $this->smarty->assign('userLog',$logData);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->display('Manage/log.html');
    }
    
    //删除日志
    public function deleteLog(){
        $this->manageModel->deleteLog();
        rewrite::succ('删除成功');
    }
}
?>