<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 *
 *   内容模块
 */
defined('LMXCMS') or exit();
use GatewayWorker\Lib\Gateway;
class MysqlModel{
    protected $sqlModel=null;
    public function __construct() {
        global $db;
        $this->sqlModel=$db;
    }
    public function getUserInfo($uid)
    {
        $result=$this->sqlModel->row("SELECT * FROM `yyg_user` WHERE id=$uid");
        if(!empty($result)){
            return $result;
        }else{
            return array();
        }
    }
}
?>