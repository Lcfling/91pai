<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   模板操作控制器
 */
defined('LMXCMS') or exit();
class TemplateAction extends AdminAction{
    public function __construct() {
        parent::__construct();
    }
    //显示所有模板
    public function index(){
       //获取所有前台模板列表
       $dirList = $this->getAllTem();
       //加入默认模板
       $this->smarty->assign('default_tem',$GLOBALS['public']['default_temdir']);
       $this->smarty->assign('temList',$dirList);
       $this->smarty->display('Template/template.html');
    }
    //返回所有模板
    private function getAllTem(){
        $dir = @opendir($this->config['template']);
        while(false != $dirname = readdir($dir)){
            if($dirname != '.' && $dirname != '..' && $dirname != 'admin'){
                $dirList[] = $dirname;
            }
        }
        return $dirList;
    }
    //查看模板文件和文件夹
    public function opendir(){
        $dir = $_GET['dir'];
        if(!$dir){
            $dir = $GLOBALS['public']['default_temdir'];
        }
        $diropen = @opendir($this->config['template'].$dir);
        while(false != $name = readdir($diropen)){
           if($name != '.' && $name != '..'){
               //判断是否为目录
               if(is_dir($this->config['template'].$dir.'/'.$name)){
                  $dirlist[] = $name; 
               }else{
                  $filelist[] = $name;
               }
           }
        }
        if($filelist){
            foreach($filelist as $k => $v){
                //设置默认后缀
                $fix = array('html','jpg','gif','png','bmp','css','js');
                $pathinfos = pathinfo($v);
                //过滤掉其他后缀文件
                if(!in_array($pathinfos['extension'],$fix)){
                    continue;
                }
                $img_fix = array('jpg','gif','png','bmp');
                if(in_array($pathinfos['extension'],$img_fix)){
                    $filelists[$k]['type'] = 1;
                    $temDirname = $this->config['template'];
                    $temDirname = str_replace(ROOT_PATH,'',$this->config['template']);
                    $filelists[$k]['imagepath'] = $temDirname.$dir.'/'.$v;
                }else{
                    $filelists[$k]['type'] = 0;
                }
                $size = filesize($this->config['template'].$dir.'/'.$v);
                $filelists[$k]['name'] = $v;
                $filelists[$k]['time'] = filemtime($this->config['template'].$dir.'/'.$v);
                $filelists[$k]['size'] = $size > 1024 ? round(($size/1024),2).'KB' : $size.'字节';
            }
        }
        if(dirname($this->config['template'].$dir) != $this->config['template']){
            $this->smarty->assign('parent',dirname($dir));
        }
        $this->smarty->assign('filelist',$filelists);
        $this->smarty->assign('dirlist',$dirlist);
        $this->smarty->assign('dir',$dir);
        $this->smarty->display('Template/temlist.html');
    }
    //编辑和查看文件与图像
    public function editfile(){
        $dir = $_GET['dir'];
        //保存修改
        if(isset($_POST['settemcontent'])){
            if($this->config['template_edit']){
                rewrite::js_back('系统设置禁止修改模板文件');
            }
            file::put($this->config['template'].$dir.'/'.$_POST['filename'],string::stripslashes($_POST['temcontent']));
            addlog('修改模板文件'.$this->config['template'].$dir);
            rewrite::succ('修改成功','?m=Template&a=opendir&dir='.$dir);
            exit();
        }
        $pathinfo = pathinfo($dir);
        //获取文件内容
        $content = string::html_char(file::getcon($this->config['template'].$dir));
        $this->smarty->assign('filename',$pathinfo['basename']);
        $this->smarty->assign('temcontent',$content);
        $this->smarty->assign('dir',dirname($_GET['dir']));
        $this->smarty->display('Template/temedit.html');
    }
    
    //切换风格
    public function changeTem(){
        if(!$_GET['tem'] || !isset($_GET['tem'])){
            tool::JSback('参数有误');
        }
        $tem = $_GET['tem'];
        $GLOBALS['public']['default_temdir'] = $tem;
        f('public/conf',$GLOBALS['public'],true);
        //清除模板编译缓存文件
        $this->smarty->compile_dir = $this->config['smy_compile_dir'].'index/'; //修改编译路径为前台
        $this->smarty->clear_compiled_tpl();
        $this->smarty->compile_dir = $this->config['smy_compile_dir'].'admin/'; //改回后台路径
        rewrite::succ();
    }
}
?>