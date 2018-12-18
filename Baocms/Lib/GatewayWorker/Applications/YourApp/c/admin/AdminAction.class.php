<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台控制器基类
 */
class AdminAction extends Action{
    protected $username;
    protected function __construct(){
        parent::__construct();
        //后台判断登录
        $this->username = LoginAction::isloginAction();
        $GLOBALS['allfield'] = category::getField();
    }
}
?>