<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   tags控制器
 */
defined('LMXCMS') or exit();
class TagsAction extends AdminAction{
    private $model = null;
    public function __construct() {
        parent::__construct();
        if($this->model == null) $this->model = new TagsModel();
    }
    
    public function index(){
        $count = $this->model->tagsCount();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->model->getData($page->returnLimit());
        $classData = category::classSelect();
        $this->smarty->assign('classData',$classData);
        $this->smarty->assign('list',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Tags/index.html');
    }
    
    public function search(){
        $name = trim($_GET['name']);
        if(!$name) rewrite::js_back('请输出要搜索的Tags名字');
        $where = 'name like "%'.$name.'%"';
        $count = $this->model->tagsCount($where);
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->model->getData($page->returnLimit(),$where);
        $this->smarty->assign('list',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('name',$name);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Tags/index.html');
    }
    
    public function update(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        if(!$id) rewrite::js_back('参数有误');
        if($_POST['update']){
            $data = $this->check();
            $this->model->update($data,$id);
            addlog('修改Tags【id：'.$id.'】');
            rewrite::succ('修改成功',$_POST['backurl']);
        }
        $data = string::html_arr_char($this->model->getOne($id),array('name','title','keywords','description'));
        $this->smarty->assign($data);
        $this->smarty->assign('tem',file::getTem('tags'));
        $this->smarty->display('Tags/update.html');
    }
    
    public function check(){
        $field = array('name','title','keywords','description','tem','display','pagenum','remen','tuijian');
        $data = d($field);
        if(!$data['name']) rewrite::js_back('Tags名称不能为空');
        if(!$data['pagenum']) $data['pagenum'] = 10;
        if(!$data['display']) $data['display'] = 0;
        if(!$data['tem']) $data['tem'] = 'index';
        return $data;
    }
    
    //绑定栏目
    public function bindclass(){
        $id = (int)$_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id'];
        $bind = (int)$_GET['bind'];
        if($_POST['bindSub']){
            $classid = (int)$_POST['classid'];
            $this->model->bindClass($id,$classid);
            addlog('Tags绑定栏目【id:'.$id.'】');
            rewrite::succ('绑定成功',$_POST['backurl']);
        }
        $this->smarty->assign('bind',$bind);
        $classData = category::classSelect();
        $this->smarty->assign('classData',$classData);
        $this->smarty->assign('id',$id);
        $this->smarty->display('Tags/bind.html');
    }
    
    //信息页面选择tags
    public function contentListTags(){
        $where = false;
        if(isset($_GET['searchTagsSub'])){
            $name = urldecode(trim($_GET['name']));
            if(!empty($name)){
                $where = "title like '%$name%'";
            }
        }
        $count = $this->model->tagsCount($where);
        $page = new page($count,100);
        $data = $this->model->getData($page->returnLimit(),$where);
        if(isset($_GET['is_name'])){
            $tagsArr = tags2arr(urldecode(trim($_GET['is_name'])));
            foreach($data as $v){
                $v['checked'] = 0;
                if(in_array($v['name'],$tagsArr)){
                    $v['checked'] = 1;
                }
                $newData[] = $v;
            }
            $this->smarty->assign('is_name',urldecode(trim($_GET['is_name'])));
        }else{
            $newData = $data;
        }
        $this->smarty->assign('list',$newData);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Tags/content_push.html');
    }
    
    //更新单条tags信息数量
    public function updateNum(){
        $id = (int)$_GET['id'];
        $this->model->updateNum($id);
        addlog('更新Tags信息数量【id：'.$id.'】');
        rewrite::succ('更新成功');
    }
    
    //更新全部tags信息数量
    public function updateAllNum(){
        $this->smarty->assign('info','更新中，请勿刷新');
        $this->smarty->display('speed.html');
        $this->model->updateAllNum();
        rewrite::speedSucc('更新完毕');
        rewrite::speedInfoBack('更新全部Tags信息数量成功');
        addlog('更新全部Tags所属信息数量');
    }
    
    //批量更新tags信息数量
    public function updateChangeNum(){
        $id = $_POST['id'];
        if(!$id) rewrite::js_back('请选择要更新信息的Tags');
        foreach($id as $v){
            $this->model->updateNum((int)$v);
        }
        addlog('批量更新Tags信息数量');
        rewrite::succ('更新成功');
    }
    
    //tags所属信息列表
    public function info(){
        $id = (int)$_GET['id'];
        $count = $this->model->infoCount($id);
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->model->getInfo($id,$page->returnLimit());
        $this->smarty->assign('list',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Tags/info.html');
    }
    
    //移除信息
    public function removeInfo(){
        $id = (int)$_GET['id'];
        $this->model->removeInfo($id);
        addlog('移除Tags信息【id：'.$id.'】');
        rewrite::succ('移除成功');
    }
    
    public function removeMore(){
        if($_POST['removeInfoMore']){
            $id = $_POST['id'];
            if(!$id) rewrite::js_back('请选择要移除的信息');
            foreach($id as $v){
                $this->model->removeInfo($v);
            }
            addlog('批量移除Tags信息');
            rewrite::succ('移除成功');
        }
    }
    
    //删除Tags
    public function delete(){
        $id = (int)$_GET['id'];
        $this->model->deleteTags($id);
        addlog('删除Tags【id：'.$id.'】');
        rewrite::succ('删除成功');
    }
    
    public function manageTags(){
        if(isset($_POST['deleteMore'])){
            $this->deleteMore();
        }else if(isset($_POST['deleteNotInfo'])){
            $this->deleteNotInfo();
        }else if(isset($_POST['updateChangeNum'])){
            $this->updateChangeNum();
        }else if(isset($_POST['remenSub']) || isset($_POST['tuijianSub'])){
            $id = $_POST['id'];
            if(!$id) rewrite::js_back('请选择要推荐的Tags');
            $type = isset($_POST['remenSub']) ? 'remen' : 'tuijian';
            $value = (int)$_POST[$type];
            foreach($id as $v){
                $this->model->push($type,$value,$v);
            }
            addlog('批量推荐Tags');
            rewrite::succ('推荐成功');
        }else if(isset($_POST['bindClassSub'])){
            $this->bindMore();
        }
    }
    //批量绑定栏目
    private function bindMore(){
        $id = $_POST['id'];
        if(!$id) rewrite::js_back('请选择要绑定的Tags');
        $classid = (int)$_POST['bindClass'];
        foreach($id as $v){
            if($v){
                $this->model->bindClass($v,$classid);
            }
        }
        addlog('批量Tags绑定栏目');
        rewrite::succ('批量绑定栏目成功');
    }
    //删除信息数量少于某个数量的tags
    private function deleteNotInfo(){
        $num = (int)$_POST['num'];
        if($num > 0){
            $this->model->deleteNotTags($num);
        }
        addlog('删除Tags信息数量少于【'.$num.'】Tags');
        rewrite::succ('删除成功');
    }
    //删除多条tags
    private function deleteMore(){
        $id = $_POST['id'];
        if(!$id) rewrite::js_back('请选择要删除的Tags');
        foreach($id as $v){
            $this->model->deleteTags($v);
        }
        addlog('批量删除Tags');
        rewrite::succ('删除成功');
    }
}
?>