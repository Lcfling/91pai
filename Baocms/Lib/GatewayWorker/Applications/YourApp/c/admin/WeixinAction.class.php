<?php 
/**
 *    
 * 
 *   后台微信对接控制器
 */
defined('LMXCMS') or exit();
class WeixinAction extends Action{
    private $columnModel = null;
    public function __construct() {
        parent::__construct();
        if(!LoginAction::isLogin())rewrite::php_url('?m=Login');
        if($this->columnModel == null) $this->columnModel = new ColumnModel(); 
    }
    
    public function config(){
        $this->smarty->assign('username',encrypt(session('adminname'),'D',$GLOBALS['public']['user_pwd_key']));
        $this->smarty->assign('classData',$this->columnModel->getInfoClass());
        $this->smarty->display('index.html');
    }
    
    public function login(){
        $fileMaxSize=get_cfg_var( "file_uploads" ) ? get_cfg_var( "upload_max_filesize" ) : $error;
        $this->smarty->assign('webport',$_SERVER['SERVER_PORT']);
        $this->smarty->assign('webdomain',$_SERVER['SERVER_NAME'].' | '.$_SERVER['SERVER_ADDR']);
        $this->smarty->assign('webparser','PHP/'.phpversion().'&nbsp;|&nbsp;'.' MySql/'.mysql_get_server_info());
        $this->smarty->assign('webSysType',getOS());
        $this->smarty->assign('webdir',ROOT_PATH);
        $this->smarty->assign('fileMaxSize',$fileMaxSize);
        $this->smarty->assign('isupdate',$this->config['is_lmxcms_update']);
        $is_install = false;
        //检测安装目录
        if(file::isDir(ROOT_PATH.'/install') || file::isDir(ROOT_PATH.'c/install')){
            $is_install = true;
        }
        $this->smarty->assign('is_install',$is_install);
        $this->smarty->display('main.html');
    }
}
?>