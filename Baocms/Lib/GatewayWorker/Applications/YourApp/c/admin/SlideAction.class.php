<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   焦点图控制器
 */
defined('LMXCMS') or exit();
class SlideAction extends AdminAction{
    private $slideModel = null;
    public function __construct() {
        parent::__construct();
        if($this->slideModel == null) $this->slideModel = new SlideModel();
    }
    
    public function index(){
        $count = $this->slideModel->slideCount();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->slideModel->get($page->returnLimit());
        $this->smarty->assign('slide',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Slide/index.html');
    }
    
    public function add(){
        if(isset($_POST['addSlide'])){
            $data = p(1,1);
            if(!$data['name']) rewrite::js_back ('焦点图名称不能为空');
            $data['content'] = string::delHtml($data['content']);
			$data['cid'] = (int)$_POST['cid'];
			$data['zid'] = (int)$_POST['zid'];
            $id = $this->slideModel->add($data);
            addlog('增加焦点图【id：'.$id.'】');
            rewrite::succ('增加成功','?m=Slide&a=index');
        }
        $this->smarty->display('Slide/add.html');
    }
    
    //修改焦点图
    public function update(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(isset($_POST['updateSlide'])){
            $updateData = p(1,1);
            if(!$updateData['name']) rewrite::js_back ('焦点图名称不能为空');
            $updateData['content'] = string::delHtml($updateData['content']);
            $this->slideModel->update($id,$updateData);
            addlog('修改焦点图【id：'.$id.'】');
            rewrite::succ('修改成功','?m=Slide&a=index');
        }
        $data = $this->slideModel->getSlideData($id);
        foreach($data as $k => $v){
            $this->smarty->assign($k,$v);
        }
        $this->smarty->display('Slide/update.html');
    }
    
    //删除焦点图
    public function delete(){
        $id = (int)$_GET['id'];
        $this->slideModel->delete($id);
        addlog('删除焦点图【id：'.$id.'】');
        rewrite::succ('删除成功');
    }
    
    //图片列表
    public function img(){
        $id = (int)$_GET['id'];
        $slidedata = $this->slideModel->getSlideData($id);
        $this->smarty->assign('slideData',$slidedata);
        $imgdata = $this->slideModel->getimg($id);
        $this->smarty->assign('img',$imgdata);
        $this->smarty->assign('id',$id);
        $this->smarty->display('Slide/img.html');
    }
    
    //增加焦点图片
    public function addimg(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(isset($_POST['addimg'])){
            $data = p(1,1);
            if(!$data['img']) rewrite::js_back('图片不能为空');
            if(!$data['uid']) rewrite::js_back('参数有误');
            $this->slideModel->addimg($data);
            addlog('增加焦点图片');
            rewrite::succ('增加图片成功','?m=Slide&a=img&id='.$data['uid']);
        }
        $data = $this->slideModel->getSlideData($id);
        $this->smarty->assign('slideData',$data);
        $this->smarty->assign('id',$id);
        $this->smarty->display('Slide/addimg.html');
    }
    
    //修改焦点图片
    public function updateimg(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        $uid = (int)$_GET['uid'] ? (int)$_GET['uid'] : (int)$_POST['uid'];
        if(isset($_POST['updateimg'])){
            $data = p(1,1);
            if(!$data['img']) rewrite::js_back('图片不能为空');
            if(!$data['uid'] || !$data['id']) rewrite::js_back('参数有误');
            $this->slideModel->updateimg($data);
            addlog('修改焦点图片');
            rewrite::succ('修改图片成功','?m=Slide&a=img&id='.$data['uid']);
        }
        $data = $this->slideModel->getSlideData($uid);
        $imgdata = $this->slideModel->getOneImg($id);
        $this->smarty->assign('img',$imgdata);
        $this->smarty->assign('slideData',$data);
        $this->smarty->assign('id',$uid);
        $this->smarty->display('Slide/updateimg.html');
    }
    
    //删除焦点图片
    public function deleteimg(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        $uid = (int)$_GET['uid'] ? (int)$_GET['uid'] : (int)$_POST['uid'];
        $this->slideModel->deleteimg($id,$uid);
        addlog('删除焦点图片');
        rewrite::succ('删除成功');
    }
    
    //焦点图片排序
    public function sortimg(){
        if(isset($_POST['sortSub'])){
            $sortArr = $_POST['sort'];
            $idArr = $_POST['id'];
            $this->slideModel->sortimg($sortArr,$idArr);
            addlog('排序焦点图片');
            rewrite::succ('排序成功');
        }
    }
}
?>