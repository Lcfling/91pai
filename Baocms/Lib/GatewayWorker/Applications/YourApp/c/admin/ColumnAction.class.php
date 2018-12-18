<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   栏目管理控制器
 */
defined('LMXCMS') or exit();
class ColumnAction extends AdminAction{
    private $columnModel = null;
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        foreach($GLOBALS['allclass'] as $v){
            switch($v['classtype']){
                case '0' :$islist = $v['islist'] == 1 ? '【列表】' : '【封面】'; $v['classtypeName']='普通栏目'.$islist; break;
                case '1' : $v['classtypeName']='<span style=" color:#f00;">单页栏目</span>'; break;
                case '2' : $v['classtypeName']='<span style=" color:blue;">外部链接栏目</span>'; break;
                default : $v['classtypeName']='获取出错'; break;
            }
            if($v['signLevel'] > 0){
                $v['lvevlImage'] = "<img src=template/admin/img/bg_columnx".$v['signLevel'].".gif />";
            }else{
                $v['lvevlImage']='';
            }
            //获取当前栏目链接地址
            $v['classurl'] = classurl($v['classid']);
            if($v['classtype'] == 2){
                $v['classpath'] = $v['classurl'];
                $v['mname'] = '<span style=" color:blue;">外部链接(系统)</span>';
            }else if($v['classtype'] == 1){
                $v['mname'] = '<span style=" color:#f00;">单页模块(系统)</span>';
                
            }
            $newData[] = $v;
        }
        $this->smarty->assign('column',$newData);
        $this->smarty->display('Column/column.html');
    }
    
    //刷新栏目缓存
    public function updateCache(){
        $this->getModel();
        $this->columnModel->scCacheFile();
        rewrite::succ('更新成功');
    }
    
    
    //返回栏目模块对象
    private function getModel(){
        if($this->columnModel == null) $this->columnModel = new ColumnModel();
    }
    
    //增加栏目视图
    public function addMain(){
        $this->getModel();
        $classData = category::classSelect();
        $moduleData = category::allmodule();
        if(!$moduleData) rewrite::error('请先创建内容模型');
        //注入模板变量
        $this->assignTem();
        $this->smarty->assign('classList',$classData);
        $this->smarty->assign('editor',edit::getEditObj()->getEdit('file/p'));
        $this->smarty->assign('moduleList',$moduleData);
        $this->smarty->display('Column/addcolumn.html');
    }
    
    //修改栏目视图
    public function updateMain(){
        $this->assignUpdateData();
        $this->smarty->display('Column/updatecolumn.html');
    }
    //复制栏目视图
    public function copyMain(){
        $this->assignUpdateData();
        $this->smarty->display('Column/copycolumn.html');
    } 
    
    //修改和复制栏目视图所需要的数据并注入变量
    private function assignUpdateData(){
        if(!(int)$_GET['id'] || !isset($GLOBALS['allclass'][(int)$_GET['id']])){
            rewrite::js_back ('参数有误');
        }else{
            $classid = (int)$_GET['id'];
        }
        $this->getModel();
        $classData = category::classSelect();
        $moduleData = category::allmodule();
        if(!$moduleData) rewrite::error('请先创建内容模型');
        $this->smarty->assign('classList',$classData);
        $this->smarty->assign('moduleList',$moduleData);
        //获取栏目数据
        $oneData = $this->columnModel->getOneClassData($classid);
        //注入模板变量
        $this->assignTem();
        //注入编辑器变量
        if($oneData['classtype'] == 1){
            $this->smarty->assign('editor',edit::getEditObj()->getEdit('file/p',$oneData['content']));
            unset($oneData['content']);
        }
        foreach($oneData as $k => $v){
            if($oneData['classtype'] == 1 && $k == 'classpath'){
                $v = str_replace('.html','',$v);
            }
            $this->smarty->assign($k,$v);
        }
    }
    //修改栏目表单接收
    public function update(){
        if(!isset($_POST['updateColumn'])) rewrite::js_back ('禁止非法提交');
        if(!(int)$_POST['classid'] || !isset($GLOBALS['allclass'][(int)$_POST['classid']])){
            rewrite::js_back ('参数有误');
        }else{
            $classid = (int)$_POST['classid'];
        }
        $data = $this->check();
        //修改栏目目录名字
        if($data['classtype'] == 0 && $data['classpath'] != $GLOBALS['allclass'][$classid]['classpath']){
           file::renames(ROOT_PATH.$GLOBALS['allclass'][$classid]['classpath'],ROOT_PATH.$data['classpath']);
        }
        //修改单页文件名字
        if($data['classtype'] == 1 && $data['classpath'] != $GLOBALS['allclass'][$classid]['classpath']){
            file::renames(ROOT_PATH.$GLOBALS['allclass'][$classid]['classpath'],ROOT_PATH.$data['classpath']);
        }
        $this->getModel();
        $this->columnModel->updateColumn($classid,$data);
        //更新缓存
        $this->columnModel->scCacheFile();
        addlog('修改【'.$data['classname'].'】栏目');
        rewrite::succ('修改成功','?m=Column');
    }
    
    //增加栏目表单接收
    public function addColumn(){
        if(!isset($_POST['addColumn'])) rewrite::js_back ('禁止非法提交');
        //验证表单并获取数据
        $data = $this->check();
        $this->getModel();
        $this->columnModel->addColumn($data);
        //生成栏目缓存文件
        $this->columnModel->scCacheFile();
        //保存日志
        addlog("增加栏目【".$data['classname']."】");
        rewrite::succ('增加栏目成功','?m=Column');
    }
    //获取模板列表并注入变量
    private function assignTem(){
        $this->smarty->assign('singleTemFile',file::getTem('single'));
        $this->smarty->assign('listTemFile',file::getTem('list'));
        $this->smarty->assign('contentTemFile',file::getTem('content'));
        $this->smarty->assign('searchTemFile',file::getTem('search'));
    }
    //验证数据
    private function check(){
        $arr = array(
            'uid' => 0,
            'classname'=>'',
            'title'=>'',
			'entitle'=>'',
            'mid' => 0,
            'classtype' => '',
            'classpath'=>'',
            'classurl'=>'',
            'listtem'=>'',
            'contem'=>'',
            'searchtem'=>'',
            'singletem'=>'',
            'description'=>'',
            'keywords'=>'',
            'sort' => 0,
            'images'=>'',
            'pagenum' => 0,
            'display' => 0,
            'islogin' => 0,
            'islist' => 0,
            'domain' => '',
        );
        $data = p(1,1);
        $arr['classname'] = $data['classname'];
		$arr['entitle'] = $data['entitle'];
        if(!$arr['classname']) rewrite::js_back('栏目名称不能为空');
        $arr['classtype'] = (int)$data['classtype'];
        //if($arr['classtype'] < 0 || $arr['classtype'] > 2) rewrite::js_back('栏目类型有误');
        $arr['uid'] = (int)$data['uid'];
        if($arr['uid'] != 0 && !isset($GLOBALS['allclass'][$arr['uid']])) rewrite::js_back('上级栏目不存在');
        if($arr['classtype'] == 0 || $arr['classtype'] == 3 || $arr['classtype'] == 4){
            //验证普通栏目数据
            $arr['mid'] = (int)$data['mid'];
            $module = category::allmodule();
            if(!$module || !isset($module[$arr['mid']])) rewrite::js_back('所属模型有误');
            $checkStr= '/^[a-zA-Z0-9_\/]+$/';
            $parentPath = $data['parentPath'];
            if(!$parentPath) $parentPath = '/';
            //rewrite::regular_back($checkStr,$parentPath,'请正确填写上级目录1');
            $classpath = trim($data['classpath'],'/');
            rewrite::regular_back($checkStr,$classpath,'请正确填写本栏目目录2');
            if($parentPath == $classpath) rewrite::js_back('本栏目目录不能与上级栏目相同');
             //修改栏目不验证目录是否存在
            if(!isset($data['updateColumn']) && file::isDir(ROOT_PATH.$classpath)) rewrite::js_back('目录 /'.$classpath.' 已存在');
            $arr['classpath'] = $classpath;
            $arr['listtem'] = $data['listtem'];
            if(!$arr['listtem']) rewrite::js_back('请选择栏目模板');
            $arr['contem'] = $data['contem'];
            if(!$arr['contem']) rewrite::js_back('请选择内容模板');
            $arr['searchtem'] = $data['searchtem'];
            $arr['pagenum'] = (int)$data['pagenum'] && (int)$data['pagenum'] > 0 ? (int)$data['pagenum'] : 10;
            $arr['islist'] = (int)$data['islist'] ? 1 : 0;
            $arr['domain'] = $data['domain'];
            $arr['islogin'] = (int)$data['islogin'] ? (int)$data['islogin'] : 0;
            $arr['isvip'] = (int)$data['isvip'] ? (int)$data['islogin'] : 0;
        }else if($arr['classtype'] == 1){
            //验证单页栏目数据
            $checkStr= '/^[a-zA-Z0-9\._\/]+$/';  
            if(!$data['singleparentPath']){ $data['singleparentPath'] = '/';}
            rewrite::regular_back($checkStr,$data['singleparentPath'],'请正确填写上级目录3');
            $singlepath = trim($data['singlepath'],'/');
            rewrite::regular_back($checkStr,$singlepath,'请正确填写单页文件名');
            if($singlepath == $data['singleparentPath']){ rewrite::js_back('文件名不能与上级栏目相同');}
            //修改栏目不验证文件是否存在
            if(!isset($data['updateColumn']) && file::isFile(ROOT_PATH.$singlepath.'.html')) rewrite::js_back('单页文件名 /'.$singlepath.'.html 已存在');
            $arr['classpath'] = $singlepath.'.html';
            $arr['singletem'] = $data['singletem'];
            if(!$arr['singletem']){ rewrite::js_back('请选择单页模板');}
            $arr['content'] = $data['content'];
            $arr['domain'] = $data['domain'];
        }else if($arr['classtype'] == 2){
            //验证外部链接栏目数据
            $arr['classurl'] = $data['classurl'];
        }
        $arr['images'] = $data['images'];
        if($arr['classtype'] != 2){
            $arr['title'] = $data['title'];
            $arr['keywords'] = str_replace('，',',',$data['keywords']);
            $arr['description'] = $data['description'];
        }
        $arr['sort'] = (int)$data['sort'] >= 0 ? (int)$data['sort'] : 0;
        $arr['display'] = (int)$data['display'] ? 1 : 0;
        return $arr;
    }
    
    //更新排序
    public function sort(){
       $data = p();
       if(!$data['classid'] || !is_array($data['classid']) || !$data['sort'] || !is_array($data['sort']) || count($data['classid']) != count($data['sort'])){
           rewrite::js_back('参数有误');
       }
       foreach($data['sort'] as $v){
           $sortTem = (int)$v;
           if($sortTem < 0){ $sortTem = 0;}
           $arr['sort'][]=$sortTem;
       }
       foreach($data['classid'] as $v){
           $classidTem = (int)$v;
           if($classidTem <= 0) rewrite::js_back('参数有误');
           $arr['classid'][]=$classidTem;
       }
       $this->getModel();
       $this->columnModel->updateSort($arr);
       addlog('更新栏目排序');
       //更新缓存
       $this->columnModel->scCacheFile();
       rewrite::succ('更新成功','?m=Column');
    }
    
    //删除栏目及所有所属子栏目和所有所属信息
    public function del(){
        $classid = (int)$_GET['id'];
        if(!isset($GLOBALS['allclass'][$classid])){
            rewrite::js_back('栏目不存在');
        }
        //获取该栏目的所有子栏目数据
        $delClass = tool::array_sort(category::getClassChild($classid,1),'signLevel','desc');
        //遍历删除
        $this->getModel();
        foreach($delClass as $v){
            $this->columnModel->delClass($v);
            $classidArr[] = $v['classid'];
        }
        //缓存文件缓存
        $this->columnModel->scCacheFile();
        addlog('删除栏目【id：'.implode(',',$classidArr).'】和所属信息');
        rewrite::succ('删除成功','?m=Column');
    }
}
?>