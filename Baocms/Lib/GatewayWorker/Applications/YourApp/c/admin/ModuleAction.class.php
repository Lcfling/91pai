<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   模型控制器
 */
defined('LMXCMS') or exit();
class ModuleAction extends AdminAction{
    private $moduleModel = null;
    private $mid;
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        if(is_array($GLOBALS['allmodule']))$this->smarty->assign('moduleData',$GLOBALS['allmodule']);
        $this->smarty->display('Module/module.html');
    }
    
    //更新模型、字段、信息表单缓存文件
    public function cachemodule(){
        $this->getModel();
        $this->moduleModel->scCacheFile();
        rewrite::succ();
    }
    
    //获取模块
    private function getModel(){
        if($this->moduleModel == null) $this->moduleModel = new ModuleModel();
    }
    
    //增加模型
    public function add(){
        if(isset($_POST['setModule'])){
            $this->getModel();
            $data = $this->check();
            unset($data['setModule']);
            if($this->moduleModel->is_module($data['tab']))rewrite::js_back('数据表已经存在');
            $this->moduleModel->add($data);
            addlog('增加系统模型【'.$data['mname'].'】');
            rewrite::succ('增加模型成功','?m=module');
        }
        $this->smarty->display('Module/addModule.html');
    }
    
    //修改模型
    public function update(){
        if(isset($_POST['updateModule'])){
            $data = $this->check();
            $this->getModel();
            $this->moduleModel->update($data);
            addlog('修改模型【'.$data['mname'].'】');
            rewrite::succ('修改成功','?m=module');
        }
        $this->getmid();
        $this->smarty->assign('moduleData',$GLOBALS['allmodule'][$this->mid]);
        $this->smarty->display('Module/updateModule.html');
    }
    
    //删除模型
    public function del(){
        $this->getmid();
        $this->getModel();
        if($this->moduleModel->is_data($this->mid)) rewrite::error('此模型存在信息，请先删除该模型下的信息后在删除此模型','',5000);
        $this->moduleModel->del($this->mid);
        addlog('删除系统模型【'.$GLOBALS['allmodule'][$this->mid]['mname'].'】');
        rewrite::succ('删除模型成功');
    }
    
    //获取mid
    private function getmid(){
        $this->mid = (int)$_POST['mid'] ? (int)$_POST['mid'] : (int)$_GET['mid'];
        if(!$this->mid || !isset($GLOBALS['allmodule'][$this->mid])) rewrite::js_back ('模型不存在');
    }
    
    //验证接收数据并返回
    private function check(){
        $data = p(1,0,0,1);
        if(!$data['mname']) rewrite::js_back('模型名字不能为空');
        if(isset($data['setModule'])){
            if(!$data['tab']) rewrite::js_back('数据表名不能为空');
            rewrite::regular_back('/^[a-zA-Z]{1}([a-zA-Z0-9]{2,10})$/',$data['tab'],'数据表名称由3-10个字母、数字组成，且以字母开头');
            $data['tab'] = strtolower($data['tab']);
        }
        return $data;
    }
}
?>