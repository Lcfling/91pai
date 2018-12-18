<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   自定义表单
 */
defined('LMXCMS') or exit();
class PayAction extends AdminAction{
    private $payModel = null;
    public function __construct() {
        parent::__construct();
        if($this->payModel == null) $this->payModel = new PayModel();
    }
    
    public function index(){
        $count = $this->payModel->countPay();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->payModel->getPayData($page->returnLimit());
        $this->smarty->assign('pay',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Pay/index.html');
    }
    
    //增加支付方式表单
    public function add(){
        if(isset($_POST['addPay'])){
            if(!$_POST['name']) rewrite::js_back('支付标识不能为空');
            if($this->payModel->checkPayName($_POST['name'])) rewrite::js_back('支付方式已经存在');
            if(!$_POST['title']) rewrite::js_back('支付方式名称不能为空');
            $this->payModel->add();
            addlog('增加自定义表单');
            rewrite::succ('增加成功','?m=Pay&a=index');
        }
        $this->smarty->display('Pay/addpay.html');
    }
    
    //修改表单
    public function update(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(isset($_POST['updateform'])){
            if(!$_POST['formname']) rewrite::js_back('表单名字不能为空');
            if(!$_POST['fieldid']) rewrite::js_back('至少选择一个“输入项”');
            if(!$_POST['must']) rewrite::js_back('至少选择一个“必填项”');
            $this->formModel->update($id);
            addlog('修改自定义表单【id：'.$id.'】');
            rewrite::succ('修改成功','?m=Form&a=index');
        }
        //获取表单数据
        $formData = $this->payModel->getOnePayData($id);
        $formData['must'] = explode(',',$formData['must']);
        $formData['fieldid'] = explode(',',$formData['fieldid']);
        $this->smarty->assign('form',$formData);
        $this->smarty->assign('field',$this->formModel->getFieldData());
        $this->smarty->display('Form/updateform.html');
    }

    //对数据进行编辑
    public function editedata(){
        $payid = (int)$_GET['payid'] ? (int)$_GET['payid'] : (int)$_POST['payid'];
        $param['where']='id='.$payid;
        $temp=$this->payModel->getOnePayData($param);
        $payform=$this->getform($payid);
        if($_POST['payedite']){
            foreach($payform as $k=>$v){
                $newdata[$k]=$_POST[$k];
            }
            $arr['data']=serialize($newdata);
            $this->payModel->updateInfoData($payid,$arr);
            rewrite::succ('数据存储成功','?m=pay&a=index');
        }
        $paydata=unserialize($temp['data']);
        if($paydata!=''){
            foreach($payform as $k=>$v){
                $payform[$k]['value']=$paydata[$k];
            }
        }
        $this->smarty->assign('payid',$payid);
        $this->smarty->assign('formdata',$payform);
        $this->smarty->display('Pay/editedata.html');
    }
    private function getform($payid){
        $payfieldModel =null;
        $payfieldModel = new PayfieldModel;
        $payform=$payfieldModel->getPayField($payid);
        foreach($payform as $value){
            $newdata[$value['pname']]=$value;
        }
        $payform= $newdata;
        return $payform;

    }
}
?>