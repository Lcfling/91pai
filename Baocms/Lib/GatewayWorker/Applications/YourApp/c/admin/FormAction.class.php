<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   自定义表单
 */
defined('LMXCMS') or exit();
class FormAction extends AdminAction{
    private $formModel = null;
    public function __construct() {
        parent::__construct();
        if($this->formModel == null) $this->formModel = new FormModel();
    }
    
    public function index(){
        $count = $this->formModel->countForm();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->formModel->getFormData($page->returnLimit());
        $this->smarty->assign('form',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Form/form.html');
    }
    
    //增加表单
    public function add(){
        if(isset($_POST['addform'])){
            if(!$_POST['formname']) rewrite::js_back('表单名字不能为空');
            if(!$_POST['fieldid']) rewrite::js_back('至少选择一个“输入项”');
            if(!$_POST['must']) rewrite::js_back('至少选择一个“必填项”');
            $this->formModel->add();
            addlog('增加自定义表单');
            rewrite::succ('增加成功','?m=Form&a=index');
        }
        //获取全部字段
        $field = $this->formModel->getFieldData();
        $this->smarty->assign('field',$field);
        $this->smarty->display('Form/addform.html');
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
        $formData = $this->formModel->one($id);
        $formData['must'] = explode(',',$formData['must']);
        $formData['fieldid'] = explode(',',$formData['fieldid']);
        $this->smarty->assign('form',$formData);
        $this->smarty->assign('field',$this->formModel->getFieldData());
        $this->smarty->display('Form/updateform.html');
    }
    //根据表单id获取内容数据
    public function getContent(){
        $id = (int)$_GET['id'];
        $count = $this->formModel->getContentCount($id);
        $page = new page($count,$this->config['page_list_num']);
        //获取字段数据
        $fieldData = $this->formModel->getFormField($id);
        $data = $this->formModel->getContent($id,$page->returnLimit(),$fieldData);
        //重新组合字段数据
        foreach($fieldData as $v){
            $fieldArr[$v['fieldname']] = $v['fieldtitle'];
        }
        foreach($data as $k => $v){
            $content[$k]['cid'] = $v['cid']; unset($v['cid']);
            $content[$k]['uid'] = $v['uid'];unset($v['uid']);
            $content[$k]['time'] = $v['time'];unset($v['time']);
            $content[$k]['ip'] = $v['ip'];unset($v['ip']);
            $content[$k]['field'] = $v;
        }
        $this->smarty->assign('id',$id);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->assign('fieldArr',$fieldArr);
        $this->smarty->assign('content',$content);
        $this->smarty->display('Form/formcon.html');
    }
    //删除表单所属内容
    public function deleteCon(){
        $this->formModel->deleteCon();
        addlog('删除表单内容【cid：'.$_GET['cid'].'】');
        rewrite::succ('删除成功');
    }
    
    //获取表单html代码
    public function getHtml(){
        $id = (int)$_GET['id'];
        $fieldData = $this->formModel->getFormField($id);
        $enctype = false;
        //遍历并根据字段类型获取html表单代码
        foreach($fieldData as $v){
            $input .= $v['fieldtitle'].'：';
            $input .= $this->fieldHtml($v)."\r\n";
            if($v['fieldtype'] == 'file')$enctype = " enctype='multipart/form-data'";
        }
        $html = "<form action='".$GLOBALS['public']['weburl']."index.php?m=Form&a=index'$enctype method='post'>\r\n";
        $html .= $input;
        $html.="<input type='hidden' name='id' value=$id />\r\n<input type='submit' value='提交' name='dyFormSub' />\r\n</form>";
        $this->smarty->assign('html',htmlspecialchars($html));
        $this->smarty->display('Form/html.html');
    }
    
    //根据字段类型返回表单html代码
    private function fieldHtml($data){
        $html = '';
        switch($data['fieldtype']){
            case 'text' : $html = "<input type='text' name='".$data['fieldname']."' />"; break;
            case 'textarea' : $html = "<textarea name='".$data['fieldname']."'></textarea>"; break;
            case 'select' : $html = "<select name='".$data['fieldname']."'><option value='值'>名字(请自己定义 多个值请复制option)</option></select>"; break;
            case 'checkbox' : $html = "<input type='checkbox' name='".$data['fieldname']."[]' value='值' />名字 (请自己定义值和名字，多个请复该段多选代码)"; break;
            case 'radio' : $html = "<input type='radio' name='".$data['fieldname']."' value='值' />名字 (请自己定义值和名字，多个请复该段多选代码)"; break;
            case 'file' : $html = "<input type='file' name='".$data['fieldname']."' />"; break;
            default : $html = "<input type='text' name='".$data['fieldname']."' />"; break;
        }
        return $html;
    }
    
    //删除表单
    public function delete(){
        $this->formModel->delete();
        addlog('删除自定义表单【id：'.(int)$_GET['id'].'】');
        rewrite::succ('删除成功');
    }
    
    //字段页面
    public function field(){
        $data = $this->formModel->getFieldData();
        $this->smarty->assign('field',$data);
        $this->smarty->display('Form/field.html');
    }
    
    //增加字段
    public function addfield(){
        if(isset($_POST['addField'])){
            $data = $this->checkField();
            if($this->formModel->isNameField($data['fieldname'])) rewrite::js_back('【'.$data['fieldname'].'】字段已经存在');
            $this->formModel->addField($data);
            addlog('增加自定义表单字段');
            rewrite::succ('增加字段成功','?m=Form&a=field');
        }
        $this->smarty->display('Form/addfield.html');
    }
    
    //删除字段
    public function deleteField(){
        $fid = (int)$_GET['fid'];
        $formData = $this->formModel->getFormData();
        foreach($formData as $v){
            $fieldArr = explode(',',$v['fieldid']);
            if($fieldArr && in_array($fid,$fieldArr)){
                rewrite::js_back('该字段存在所属表单，请先删除该字段所属表单');
            }
        }
        $this->formModel->deleteField($fid);
        addlog('删除自定义表单字段');
        rewrite::succ('删除字段成功','?m=Form&a=field');
    }
    
    //验证字段并返回数据
    private function checkField(){
        $data = p(1,0,0);
        $arr['fieldname'] = strtolower($data['fieldname']);
        if(!$arr['fieldname']){
            rewrite::js_back('字段名称不能为空');
        }
        rewrite::regular_back('/^[a-z]{1}[a-zA-Z0-9]{2,8}$/',$arr['fieldname'],'由3-8个字母、数字组成且必须以字母开头');
        mysql_retain(array($data['fieldname']));
        $arr['fieldtitle'] = $data['fieldtitle'];
        if(!$arr['fieldtitle']) rewrite::js_back('字段标识不能为空');
        $arr['fieldtype'] = $data['fieldtype'];
        if(!in_array($arr['fieldtype'],array('text','textarea','select','checkbox','radio','file'))){
            rewrite::js_back('表单类型有误');
        }
        return $arr;
    }
}
?>