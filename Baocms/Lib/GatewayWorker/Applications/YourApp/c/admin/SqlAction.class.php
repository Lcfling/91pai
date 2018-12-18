<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   执行sql语句
 */
defined('LMXCMS') or exit();
class SqlAction extends AdminAction{
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        $this->smarty->display('Sql/index.html');
    }
    
    public function sqlset(){
        $sql = trim($_POST['sqlstr']);
        if(!$sql) rewrite::js_back('sql语句不能为空');
        $model = new SqlModel();
        $model->sql($sql);
        rewrite::succ('执行Sql成功');
    }
}
?>