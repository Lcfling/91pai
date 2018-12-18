<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   数据库备份控制器
 */
defined('LMXCMS') or exit();
class BackdbAction extends AdminAction{
    private $backdbModel = null;
    public function __construct() {
        parent::__construct();
        if($this->backdbModel == null){
            $this->backdbModel = new BackdbModel();
        }
    }
    
    public function index(){
        $tabData = $this->backdbModel->ShowTable();
        $this->smarty->assign('tables',$tabData);
        $this->smarty->display('Back/index.html');
    }
    
    //备份数据
    public function backdbUp(){
        if($_POST['tabname']){
            $size = (int)$_POST['backsize'] && (int)$_POST['backsize'] > 0 ? (int)$_POST['backsize'] : 2048;
            //设置php执行时间
            set_time_limit(0);
            $index = 1;
            $filename = 'backdb_lmxcms_'.date('YmdHis').rand(10000,99999);
            $this->smarty->assign('info','备份数据中，请勿刷新，否则会导致数据备份不完全');
            $this->smarty->display('speed.html');
            $is_group = 0;
            foreach($_POST['tabname'] as $v){
                $v = str_replace(DB_PRE,'',$v);
                //获取数据表结构信息
                $str .= $this->tableComOne($v);
                //分组获取数据表信息
                $count = $this->backdbModel->getCount($v);
                $group_num = 2000;
                $group = ceil($count/$group_num);
                $k = 0;
                for($i=0;$i<$group;$i++){
                    $k = $i+1;
                    $limit = $i * $group_num .','.$group_num;
                    //获取数据表数据信息
                    $str .= $this->tableOneData($v,$limit);
                    if(strlen($str) > $size * 1024){
                        $this->setFile($str,$filename.'_'.$index.'.sql');
                        $setname[] = ROOT_PATH.'file/back/'.$filename.'_'.$index.'.sql';
                        $str = '';
                        $index++;
                        $is_group = 1;
                    }
                    rewrite::speed('【'.$v.'】数据表第 <span class="red">'.$k.'</span> 组备份成功..........');
                }
                //输出备份进度
                rewrite::speed('【'.$v.'】数据表备份成功..........');
            }
            if(!$is_group){
                $this->setFile($str,$filename.'.sql');
                $setname[] = ROOT_PATH.'file/back/'.$filename.'.sql';
            }
            //打包
            rewrite::speed('正在打包数据，请稍等..........');
            $zipname = ROOT_PATH.'file/back/'.$filename.'.zip';
            file::unLink($zipname);
            zip::toZip($setname,$zipname);
            rewrite::speed('打包成功..........');
            foreach($setname as $v){
                file::unLink($v);
            }
            //输出备份成功信息
            rewrite::speedSucc('备份数据库成功');
            rewrite::speedInfoBack('数据库备份成功');
            //恢复php默认执行时间
            addlog('备份数据库');
        }else{
            rewrite::js_back('请选择要备份的数据表');
        }
    }
    
    //把sql保存到文件
    private function setFile($str,$filename){
        if($str){
            $lmxcms = "#---------------------------------------------------------#\r\n# LMXCMS \r\n# version:".$GLOBALS['public']['version']." \r\n# Time: ".date('Y-m-d H:i:s')." \r\n# http://www.lmxcms.com \r\n# --------------------------------------------------------#\r\n\r\n\r\n";
            $str = $lmxcms.$str;
            file_put_contents(ROOT_PATH.'file/back/'.$filename,$str);
        }
    }
    
    //恢复数据列表页面
    public function backdbInList(){
        $dir = ROOT_PATH.'file/back/';
        $file = $this->getBackDirFile('zip');
        $fileInfo = array();
        if($file){
            foreach($file as $k => $v){
                $time = filemtime($dir.$v);
                $fileInfo[$time]['time'] = $time;
                $fileInfo[$time]['filename'] = $v;
                $siez = round(filesize($dir.$v) / 1024);
                $fileInfo[$time]['filesize'] = $siez > 1024 ? round($siez / 1024,2).'MB' : $siez.'KB';
                $fileInfo[$time]['fjfile'] = zip::fileNum($dir.$v);
            }
        }
        //按照备份时间倒序排列
        krsort($fileInfo);
        $this->smarty->assign('backlist',$fileInfo);
        $this->smarty->display('Back/inlist.html');
    }
    
    //返回备份目录下所有的文件
    private function getBackDirFile($fix='sql'){
        $dir = ROOT_PATH.'file/back';
        if(!file_exists($dir)){
            rewrite::js_back('备份目录不存在，请手动创建'.$dir);
        }
        //获取目录下所有文件
        $fileArr = array();
        $op = opendir($dir);
        while(false!=$file=readdir($op)){
            if($file!='.' &&$file!='..'){
                if(preg_match('/\.'.$fix.'$/',$file)) $fileArr[] = $file;
            }
        }
        closedir($op);
        return $fileArr;
    }
    //恢复备份
    public function backdbin(){
        set_time_limit(0); //不限制php执行时间
        $dir = ROOT_PATH.'file/back/';
        $filename = trim($_GET['filename']);
        if(!file::isFile($dir.$filename)){
            rewrite::js_back('备份文件不存在');
        }
        $this->smarty->assign('info','数据库恢复中，请勿刷新，否则会导致恢复出错');
        $this->smarty->display('speed.html');
        rewrite::speed('正在解压文件，请稍后');
        //开始解压
        zip::openZip($dir.$filename,$dir);
        $all = $this->getBackDirFile();
        //获取符合的文件
        $file = $this->getAllFile($all,str_replace('.zip','',$filename));
        rewrite::speed('解压文件成功，开始恢复数据');
        //遍历恢复
        foreach($file as $k => $v){
            $sql = '';
            $filepath = $dir.$v;
            $sql = file($filepath);
            $this->queryIn($sql);
            rewrite::speed('恢复第 <span class="red">'.$k.'</span> 卷成功');
            file::unLink($filepath); //删除该卷
        }
        rewrite::speedSucc('恢复数据库成功');
        rewrite::speedInfoBack('恢复数据库成功');
        addlog('恢复数据库备份');
    }
    
    //根据文件名获取所有sql文件分卷
    private function getAllFile($allfile,$filename){
        $file = array();
        foreach($allfile as $v){
            if(!(strpos($v,rtrim($filename,'.sql')) === false)){
                preg_match('/^backdb_lmxcms_[\d]+_([\d]+)/',$v,$k);
                $k[1] = $k[1] ? $k[1] : 1;
                $file[$k[1]] = $v;
            }
        }
        ksort($file);
        return $file;
    }
    
    //执行数组中的sql语句
    private function queryIn(array $sql){
        $query = '';
        foreach($sql as $v){
            if(!$v || $v[0] == '#'){
                continue;
            }else if(preg_match('/\[--end--\]$/',trim($v))){
                $v = preg_replace('/\[--end--\]$/','',trim($v));
                $query .= $v;
                if(preg_match('/^DROP TABLE IF EXISTS (.*)\;/',trim($v),$name)){
                    rewrite::speed('正在恢复【'.$name[1].'】数据表');
                }
                $this->backdbModel->backSql($query);
                $query = '';
            }else{
                $query .= $v;
            }
        }
    }
    
    //返回一张表结构
    private function tableComOne($tabname){
        $str = "DROP TABLE IF EXISTS ".DB_PRE."$tabname;[--end--]\r\n";
        $tabData = $this->backdbModel->getTableCom($tabname);
        $tabData[0]['Create Table'] = str_replace($tabData[0]['Table'],DB_PRE.$tabname,$tabData[0]['Create Table']);
        return $str.=$tabData[0]['Create Table'].";[--end--]\r\n";
    }
    
    //分组返回一张表的数据并组合sql语句
    private function tableOneData($tabname,$limit){
        $data = $this->backdbModel->getData($tabname,$limit);
        $data = $data ? $data : array();
        foreach($data as $v){
            $s = array();
            $insertStr .= "INSERT INTO ".DB_PRE."$tabname VALUES(";
            foreach($v as $str){
                $s[] .= addslashes($str);
            }
            $insertStr .= "'".implode("','",$s)."'";
            $insertStr .= ");[--end--]\r\n";
        }
        return $insertStr;
    }
    
    //删除备份文件
    public function delbackdb(){
        $filename = trim($_GET['filename']);
        if(!$filename){
            rewrite::js_back('备份文件不存在');
        }
        $this->delOne($filename);
        addlog('删除数据库备份文件');
        rewrite::succ('删除成功');
    }
    
    //批量删除备份文件
    public function delmorebackdb(){
        $filename = $_POST['filename'];
        if($filename){
            foreach($filename as $v){
                $this->delOne($v);
            }
            addlog('批量删除数据库备份文件');
            rewrite::succ('删除成功');
        }else{
            rewrite::js_back('请选择要删除的备份文件');
        }
    }
    
    //根据文件名删除一条备份文件
    private function delOne($filename){
        $dir = ROOT_PATH.'file/back/'.$filename;
        file::unLink($dir);
    }
}
?>