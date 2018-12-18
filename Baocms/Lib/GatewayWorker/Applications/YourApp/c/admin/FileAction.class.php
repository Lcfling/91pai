<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   文件管理控制器
 */
class FileAction extends AdminAction{
    private $type; //图片 or 文件
    private $fileModel = null;
    public function __construct() {
        parent::__construct();
        $this->type = (int)$_POST['type'] ? (int)$_POST['type'] : (int)$_GET['type'];
        if($this->fileModel == null) $this->fileModel = new FileModel();
    }
    
    //列表
    public function index(){
       $count = $this->fileModel->count($this->type);
       $page = new page($count,20);
       $data = $this->fileModel->getData($this->type,$page->returnLimit());
       $this->smarty->assign('num',$count);
       $this->smarty->assign('page',$page->html());
       $this->smarty->assign('file',$data);
       if($this->type){
           $this->smarty->display('File/file.html');
       }else{
           $this->smarty->display('File/image.html');
       }
    }
    
    //删除
    public function delete(){
        if(!$_POST['fid']) rewrite::js_back('请选择要删除的文件');
        $this->fileModel->delete($_POST);
        addlog('删除文件、图片');
        rewrite::succ('删除成功');
    }
    
}
?>