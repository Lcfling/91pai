<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   文件上传控制器
 */
defined('LMXCMS') or exit();
class UploadAction extends AdminAction{
    private $dir; //上传路径
    private $id; //返回地址的id
    private $ismore; //是否多图片选择
    public function __construct() {
        parent::__construct();
    }
    
     //上传图片视图
    public function img(){
        $this->getParam();
        $this->smarty->display('Upload/uploadimages.html');
    }
    
    //上传文件视图
    public function file(){
        $this->getParam();
        $this->smarty->display('Upload/uploadfile.html');
    }
    
    //图片列表视图
    public function imageList(){
        //注入变量
        $this->getParam();
        //获取数据
        $fileModel = new FileModel();
        $num = $fileModel->count(0);
        $page = new page($num,12);
        $list = $fileModel->getData(0,$page->returnLimit());
        $ismore = $this->ismore ? '&ismore=1' : '';
        $this->smarty->assign('upload_url',$_SERVER['SCRIPT_NAME'].'?m=Upload&a=img&dir='.$this->dir.'&field='.$this->id.$ismore);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Upload/imagelist.html');
    }
    
    //文件列表视图
    public function fileList(){
        //注入变量
        $this->getParam();
        //获取数据
        $fileModel = new FileModel();
        $num = $fileModel->count(1);
        $page = new page($num,10);
        $list = $fileModel->getData(1,$page->returnLimit());
        $ismore = $this->ismore ? '&ismore=1' : '';
        $this->smarty->assign('upload_url',$_SERVER['SCRIPT_NAME'].'?m=Upload&a=file&dir='.$this->dir.'&field='.$this->id.$ismore);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Upload/filelist.html');
    }
    
    //获取变量参数并注入变量
    private function getParam(){
        if(!$_GET['dir'] || !$_GET['field'])rewrite::js_back('参数有误');
        $this->dir = $_GET['dir'];
        $this->id = $_GET['field'];
        $this->ismore = $_GET['ismore'] ? 1 : 0;
        $this->smarty->assign('img_fix',implode('、',$this->config['upload_image_pre']));
        $this->smarty->assign('file_fix',implode('、',$this->config['upload_file_pre']));
        foreach($this->config['upload_image_pre'] as $v){
            $js_img_pre[] = '*.'.$v;
        }
        foreach($this->config['upload_file_pre'] as $v){
            $js_file_pre[] = '*.'.$v;
        }
        $this->smarty->assign('js_img_fix',implode(';',$js_img_pre));
        $this->smarty->assign('js_file_fix',implode(';',$js_file_pre));
        $this->smarty->assign('file_head',$_SERVER['SCRIPT_NAME']);
        $this->smarty->assign('update_max_size',$this->config['update_max_size']);
        $this->smarty->assign('uploadDir',$this->dir);
        $this->smarty->assign('ismore',$this->ismore);
        $this->smarty->assign('selectid',$this->id);
    }
    
    //接收swfupload上传的图片
    public function swfuploadImg(){
       $path = $_POST['upload_path'];
       if(!$path){
           echo json_encode(array('succ' => 0, 'info' => '上传目录有误'));
           exit;
       }
       $img_water = $_POST['img_water'] ? true : false;
       $img_small = $_POST['img_small'] ? true : false;
       $height_bl = $_POST['height_bl'] ? true : false;
       $img_width = (int)$_POST['img_width'];
       $img_height = (int)$_POST['img_height'];
       $config['fix'] = $this->config['upload_image_pre'];
       $upload = new upload();
       $upload->setConfig('fix',$config['fix']);
       if($upload->upload($path)){
           $fileModel = new FileModel();
           $info = $upload->getSucc();
           $s = array('succ' => 1 , 'info' => $info[0]);
           //缩略图
           if($img_small){
               $small_path = $this->ImgSmall($info[0]['pre'].'/'.$info[0]['file'],$img_width,$img_height,$height_bl);
               //保存缩略图到数据库
			   $file['size'] = filesize(ROOT_PATH.ltrim($s['smallsrc'],'\/'));
               $file['filename'] = $info[0]['filename'];
               $file['name'] = $info[0]['name'];
               $file['url'] = $GLOBALS['public']['weburl'].trim($small_path,'/');
               $file['issmall'] = 1;
               $fileModel->add($file,0);
							 $s['smallsrc'] = $GLOBALS['public']['weburl'].trim($small_path,'/');
           }
           //加水印
           if($img_water){
               $this->imgWater($info[0]['pre'].'/'.$info[0]['file']);
           }
           //增加到数据库
           $fileModel->add($info[0],0);
       }else{
           $s = array('succ' => 0 , 'info' => $upload->getMsg());
       }
       echo json_encode($s);
    }
    
    //生成图片缩略图
    private function ImgSmall($path,$width,$height,$height_bl){
        //如果没给高度和宽度，则采用系统默认
        $width = $width ? $width : $GLOBALS['public']['small_width'];
        $height = $height ? $height : $GLOBALS['public']['small_height'];
        $src = pathinfo(image::smallImage(ROOT_PATH.$path,$width,$height,$height_bl));
        return '/'.dirname($path).'/'.$src['basename'];
    }
    
    //图片水印
    private function imgWater($path){
        image::addWater(ROOT_PATH.$path,ROOT_PATH.trim($GLOBALS['public']['markImg'],'/'));
    }
    
    //接收swfupload上传的文件
    public function swfuploadFile(){
       $path = $_POST['upload_path'];
       if(!$path){
           echo json_encode(array('succ' => 0, 'info' => '上传目录有误'));
           exit;
       }
       $config['fix'] = $this->config['upload_file_pre'];
       $upload = new upload();
       $upload->setConfig('fix',$config['fix']);
       if($upload->upload($path)){
           $fileModel = new FileModel();
           $info = $upload->getSucc();
           $s = array('succ' => 1 , 'info' => $info[0]);
           //增加到数据库
           $fileModel->add($info[0],1);
       }else{
           $s = array('succ' => 0 , 'info' => $upload->getMsg());
       }
       echo json_encode($s);
    }
}
?>