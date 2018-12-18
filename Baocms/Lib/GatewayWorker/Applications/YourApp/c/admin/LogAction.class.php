<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   日志控制器
 */
defined('LMXCMS') or exit();
class LogAction extends AdminAction{
    private $logManage = null;
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        $logManage = new LogModel();
        //处理分页
        $count = $logManage->getLogCount();
        $page = new page($count,$this->config['page_list_num']);
        $pageHtml = $page->html();
        //获取数据
        $param = array(
            'limit' => $page->returnLimit(),
            'order' => 'id desc',
        );
        $logData=$logManage->getLog($param);
        $this->smarty->assign('logData',$logData);
        $this->smarty->assign('page',$pageHtml);
        $this->smarty->display('Log/index.html');
    }
    
    //删除7天以外所有日志
    public function del(){
        if(!isset($_POST['dellog'])) rewrite::js_back ('禁止非法提交');
        $logManage = new LogModel();
        if(!$logManage->delLog()){
            rewrite::error('删除失败，请重试','?m=Log');
        }
        addlog('清理全部日志（除了7日内的日志）');
        rewrite::succ('删除成功');
    }
}
?>