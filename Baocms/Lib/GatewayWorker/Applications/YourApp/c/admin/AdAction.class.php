<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   广告控制器
 */
defined('LMXCMS') or exit();
class AdAction extends AdminAction{
    private $adModel = null;
    public function __construct() {
        parent::__construct();
        if($this->adModel == null) $this->adModel = new AdModel();
    }
    
    //列表
    public function index(){
        $extime = '';
        if($_GET['extime']){
            $extime = 'extime < '.(time() + ($this->config['ad_extime'] * 24 * 3600));
            $this->smarty->assign('extime',1);
        }
				$count = $this->adModel->count($extime);
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->adModel->getData($page->returnLimit(),$extime);
        if($data){
            foreach($data as $v){
                $v['type'] = $this->type($v['type']);
                $newData[] = $v;
            }
        }
	$this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('ad',$newData);
        $this->smarty->display('Ad/ad.html');
    }
    
    //返回广告类型文字
    private function type($type){
        $str = '';
        switch($type){
            case 0 : $str = '图片';
			break;
            case 1 : $str = '文字';
			break;
            case 2 : $str = 'html';
			break;
            default : $str = '错误';
			break;
        }
        return $str;
    }
    
    //修改
    public function update(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(isset($_POST['updatead'])){
            $updateData = $this->check();
            $this->adModel->update($updateData,$id);
            addlog('修改广告');
            rewrite::succ('修改成功','?m=Ad');
        }
        $data = $this->adModel->one($id);
        if($data['type'] == 2){
            $data['html'] = string::html_char(string::stripslashes($data['html']));
        }
        $this->smarty->assign('ad',$data);
        $this->smarty->display('Ad/updatead.html');
    }
    
    //删除
    public function delete(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        $this->adModel->delete($id);
        addlog('删除广告【id：'.$id.'】');
        rewrite::succ('删除广告成功');
    }
    
    //增加
    public function add(){
        if(isset($_POST['addad'])){
            $data = $this->check();
            $this->adModel->add($data);
            addlog('增加广告');
            rewrite::succ('增加广告成功','?m=Ad');
        }
        $this->smarty->display('Ad/addad.html');
    }
    
    //验证数据
    private function check(){
        $name = $_POST['name'];
        if(!$name)rewrite::js_back('广告名称不能为空');
        $type = (int)$_POST['type'];
        if($type != 0 && $type != 1 && $type !=2) rewrite::js_back('广告类型有误');
        $extime = $_POST['extime'];
        if(!$extime)rewrite::js_back('到期时间不能为空');
        $extime = strtotime($extime);
        $arr['name'] = $name;
        $arr['type'] = $type;
        $arr['extime'] = $extime;
        $arr['width'] = 0;
        $arr['height'] = 0;
        $arr['string'] = '';
        $arr['html'] = '';
        $arr['img'] = '';
        $arr['exstr'] = '';
        $arr['http'] = '';
        //根据类型验证
        if($type == 0){
            //验证图片数据
            $arr['img'] = $_POST['img'];
            if(!$arr['img'])rewrite::js_back('图片不能为空');
            $arr['http'] = $_POST['http'];
            if(!$arr['http'] || !preg_match('/^http:\/\//',$arr['http'])){
                rewrite::js_back('请正确填写链接地址');
            }
            $arr['width'] = (int)$_POST['width'];
            $arr['height'] = (int)$_POST['height'];
        }else if($type == 1){
            //验证文字数据
            $arr['string'] = $_POST['string'];
            if(!$arr['string'])rewrite::js_back('请填写文字');
            if(!preg_match('/#####/',$arr['string']))rewrite::js_back('请检查文字格式是否正确');
        }else if($type == 2){
            $html = $_POST['html'];
            if(!$html)rewrite::js_back('html代码不能为空');
            $arr['html'] = string::addslashes($html);
        }
        $arr['exstr'] = $_POST['exstr'];
        $arr['remarks'] = $_POST['remarks'];
        return $arr;
    }
    
    public function cacheAd(){
        $this->adModel->adCache();
        rewrite::succ();
    }
}
?>