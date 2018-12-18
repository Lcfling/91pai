<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   字段操作控制器
 */
defined('LMXCMS') or exit();
class PayfieldAction extends AdminAction{
    private $payfieldModel = null;
    private $payid;
    private $fid;
    public function __construct() {
        parent::__construct();
        if($this->payfieldModel==null) {
            $this->payfieldModel = new PayfieldModel();
        }
        if($_GET['payid']==''){
            rewrite::js_back('找不到支付方式'.$this->payid);
        }
        $this->payid=(int)$_GET['payid'];
    }
    
    //列表视图
    public function index(){
        $Payfield = $this->payfieldModel->getPayField($this->payid);
        $this->smarty->assign('payid',$this->payid);
        $this->smarty->assign('payfield',$Payfield);
        $this->smarty->display('Pay/payfield.html');
    }
    
    //增加字段
    public function add(){
        if(isset($_POST['addPayField'])){
            $this->payfieldModel->add($data);
            addlog('增加支付方式格式化数据');
            rewrite::succ('增加格式化数据成功','?m=payfield&a=index&payid='.$this->payid);
        }
        $this->smarty->assign('payid',$this->payid);
        $this->smarty->display('Pay/addpayfield.html');
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
            rewrite::regular_back('/^[a-zA-Z]{1}([a-zA-Z0-9]{2,10})$/',$data['fname'],'字段名称必须由3-10字母、数字组成，并且仅能字母开头');
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