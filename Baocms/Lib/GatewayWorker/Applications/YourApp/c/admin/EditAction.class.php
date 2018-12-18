<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台编辑器上传文件、图片辅助
 */
defined('LMXCMS') or exit();
class EditAction extends AdminAction{
    public function __construct() {
        parent::__construct();
    }
    //编辑器上传提交方法  
    public function editUpload(){
        $path = trim($_GET['path']);
        $config['fix'] = array_merge($this->config['upload_file_pre'],$this->config['upload_image_pre']);
        $config['maxSize'] = $this->config['update_max_size'];
        edit::getEditObj()->upload($path,$config);
    }
}
?>
