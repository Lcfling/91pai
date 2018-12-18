<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   字段操作控制器
 */
defined('LMXCMS') or exit();
class FieldAction extends AdminAction{
    private $fieldModel = null;
    private $mid;
    private $fid;
    public function __construct() {
        parent::__construct();
        $this->mid = (int)$_POST['mid'] ? (int)$_POST['mid'] : (int)$_GET['mid'];
        if(!$this->mid || !isset($GLOBALS['allmodule'][$this->mid])) rewrite::js_back('模型id不存在');
        $this->fid = (int)$_POST['fid'] ? (int)$_POST['fid'] : (int)$_GET['fid'];
        if($this->fieldModel == null) $this->fieldModel = new FieldModel();
        $this->smarty->assign('modData',$GLOBALS['allmodule'][$this->mid]);
    }
    
    //列表视图
    public function index(){
        $fieldData = $GLOBALS['allfield'][$this->mid];
        $this->smarty->assign('fieldData',$fieldData);
        $this->smarty->display('Module/field.html');
    }
    
    //增加字段
    public function add(){
        if(isset($_POST['addField'])){
            $data = $this->check();
            $this->fieldModel->add($data);
            addlog('增加字段—模型id：【'.$this->mid.'】—字段名字：【'.$data['fname'].'】');
            rewrite::succ('增加字段成功','?m=Field&a=index&mid='.$this->mid);
        }
        $this->smarty->display('Module/addfield.html');
    }
    
    //修改
    public function update(){
        $fieldData = $this->isfield(); //验证字段是否存在，并返回字段数据
        if(isset($_POST['updateField'])){
            $data = $this->check();
            $this->fieldModel->updateField($data);
            addlog('修改字段【fid：'.$data['fid'].'】');
            rewrite::succ('修改字段成功','?m=Field&a=index&mid='.$this->mid);
        }
        $this->smarty->assign('fieldData',$fieldData);
        $this->smarty->display('Module/updatefield.html');
    }
    
    //删除
    public function del(){
        $fData = $this->isfield();
        $this->fieldModel->deleteField($this->mid,$this->fid,$fData);
        addlog('删除字段【mid：'.$this->mid.'、fid:'.$this->fid.'】');
        rewrite::succ('删除字段成功');
    }
    
    //字段排序
    public function sortField(){
        if(!isset($_POST['sortSub'])) rewrite::js_back('禁止非法提交');
        if(!isset($GLOBALS['allmodule'][$_POST['mid']])) rewrite::js_back('模型不存在');
        $this->fieldModel->sort();
        rewrite::succ('排序成功');
    }
    
    //根据fid判断字段是否存在并返回数据
    private function isfield(){
        $fidData = $GLOBALS['allfield'][$this->mid];
        if(!$fidData[$this->fid]){
            rewrite::js_back('字段不存在');
        }
        return $fidData[$this->fid];
    }
    
    //验证并返回数据
    private function check(){
        $data = p(1,1,0,1);
        if(!(int)$data['mid']) rewrite::js_back('参数有误');
        $blArr = array('id','classid','keywords','description','time','url','tuijian','remen','click','title','ztid');
        if(isset($data['addField'])){
            if(!$data['fname']) rewrite::js_back('字段名称不能为空');
            rewrite::regular_back('/^[a-zA-Z]{1}([a-zA-Z0-9]{2,16})$/',$data['fname'],'字段名称必须由3-16字母、数字组成，并且仅能字母开头');
            $data['fname'] = strtolower($data['fname']);
            //验证系统保留字段
            if(in_array($data['fname'],$blArr)){ rewrite::js_back('【'.$data['fname'].'】为系统保留字段');} 
            //验证字段是否存在
            if($this->fieldModel->is_fname($data['fname'],$data['mid'])) rewrite::js_back('字段已存在，请换个字段名字');
            $type = array('text','pwd','textarea','editor','selects','checkbox','radio','image','moreimage','file','morefile','date');
            if(!in_array($data['ftype'],$type)) rewrite::js_back('请正确选择表单类型');
        }
        if(!$data['ftitle']) rewrite::js_back('字段标识不能为空');
        $data['sort'] = (int)$data['sort'];
        $data['ismust'] = $data['ismust'] ? 1 : 0;
        return $data;
    }
}
?>