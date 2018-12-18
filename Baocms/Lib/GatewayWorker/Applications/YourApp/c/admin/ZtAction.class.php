<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   专题控制器
 */
defined('LMXCMS') or exit();
class ZtAction extends AdminAction{
    private $model = null;
    public function __construct() {
        parent::__construct();
        if($this->model == null) $this->model = new ZtModel();
    }
    
    public function index(){
        $count = $this->model->count();
        $page = new page($count,$this->config['page_list_num']);
        $assign = array(
            'num' => $count,
            'page' => $page->html(),
            'list' => $this->model->getData($page->returnLimit()),
        );
        $this->smarty->assign($assign);
        $this->smarty->display('Zt/index.html');
    }
    
    public function search(){
        $name = trim($_GET['name']);
        if(!$name) rewrite::js_back('请输入专题名称');
        $where = "name like '%$name%'";
        $count = $this->model->count($where);
        $page = new page($count,$this->config['page_list_num']);
        $assign = array(
            'num' => $count,
            'page' => $page->html(),
            'list' => $this->model->getData($page->returnLimit(),$where),
            'name' => $name,
        );
        $this->smarty->assign($assign);
        $this->smarty->display('Zt/index.html');
    }
    //ajax获取专题列表
    public function ajax_data(){
        $curr = $_POST['curr'];
        $name = trim($_POST['name']);
        $where = '';
        if($name){ $where = "name like '%$name%'";}
        $count = $this->model->count($where);
        $page_num = 7;
        $limit = ($curr-1) * $page_num .",$page_num";
        $assign = array(
            'list' => $this->model->getData($limit,$where),
            'count' => $count,
            'page' => ceil($count/$page_num),
        );
        echo json_encode($assign);
    }
    
    //信息单条推送
    public function content_push(){
        if($_GET['is_ztid']){
            $is_ztid = trim(trim($_GET['is_ztid']),',');
            $where = 'id in('.$is_ztid.')';
            $data = $this->model->getData(100,$where);
            $this->smarty->assign('is_data',$data);
        }
        $this->smarty->display('Zt/content_push.html');
    }
    
    //信息列表推送
    public function list_push(){
        if(isset($_POST['push_ok'])){
            if(!$_POST['id']) rewrite::js_back('请选择要推送的专题');
            if(!$_POST['idStr'] || !$_POST['classid']) rewrite::js_back('参数有误');
            $this->model->pustMore($_POST['classid'],$_POST['id'],$_POST['idStr']);
            addlog('批量推送信息到专题');
            rewrite::succ('推送成功');
        }
        $classid = (int)$_GET['classid'];
        $idStr = $_GET['id'];
        if(!$classid) echo "<script type='text/javascript'>alert('栏目id有误');parent.window.close();</script>";
        if(!$idStr) echo "<script type='text/javascript'>alert('请选择要推送的信息');parent.window.close();</script>";
        $where = '';
        if(isset($_GET['searchSub'])){
            //搜索
            $name = trim($_GET['name']);
            $where = "name like '%$name%'";
        }
        $count = $this->model->count($where);
        $page = new page($count,10);
        $assign = array(
            'num' => $count,
            'page' => $page->html(),
            'classid' => $classid,
            'idStr' => $idStr,
            'list' => $this->model->getData($page->returnLimit(),$where),
        );
        $this->smarty->assign($assign);
        $this->smarty->display('Zt/list_push.html');
    }
    
    public function info(){
        $id = (int)$_GET['id'];
        $count = $this->model->infoCount($id);
        $page = new page($count,$this->config['page_list_num']);
        $assign = array(
                'list' => $this->model->infoList($id,$page->returnLimit()),
                'num' => $count,
                'page' => $page->html(),
            );
        $this->smarty->assign($assign);
        $this->smarty->display('Zt/info.html');
    }
    
    //移除单条专题信息id
    public function removeInfo(){
        $id = $_GET['id'];
        if(!$id) rewrite::js_back('请选择要移除专题信息');
        $this->model->remove($id);
        addlog('移除专题信息');
        rewrite::succ('移除成功');
    }
    
    //移除多条专题信息
    public function removeMore(array $idArr){
        $this->model->remove($idArr);
        addlog('批量移除专题信息');
        rewrite::succ('移除成功');
    }
    
    public function add(){
        if(isset($_POST['add'])){
           $data = $this->check();
           $this->model->add($data);
           addlog('增加专题');
           rewrite::succ('增加专题成功',$_POST['backurl']);
        }
        $this->smarty->assign('tem',file::getTem('zt'));
        $this->smarty->display('Zt/add.html');
    }
    
    //修改专题
    public function update(){
        if(isset($_POST['update'])){
            $data = $this->check();
            $id = $_POST['id'];
            $backpath = $_POST['back_path'];
            if($data['path'] != $backpath){
                if(file::isDir($data['path']) || $this->model->is_path($data['path'])) rewrite::js_back('专题目录已经存在');
                //修改专题目录文件夹
                file::renames(ROOT_PATH.$backpath,ROOT_PATH.$data['path']);
            }
            $this->model->update($id,$data);
            addlog('修改专题【id：'.$id.'】');
            rewrite::succ('修改成功',$_POST['backurl']);
        }
        $id = (int)$_GET['id'];
        $data = $this->model->getOne($id);
        $data = string::html_arr_char($data,array('name','title','keywords','description'));
		$this->smarty->assign('updatetem',file::getTem('zt'));
        $this->smarty->assign($data);
        $this->smarty->display('Zt/update.html');
    }
    
    //验证专题数据
    private function check(){
        $field = array('name','tem','path','islist','images','display','title','keywords','description','pagenum','sort','domain');
        $data = d($field,1,array('display'));
        if(!$data['name']) rewrite::js_back('专题名称不能为空');
        if(!$data['tem']) rewrite::js_back('请选择模板');
        if(!$data['path']) rewrite::js_back('专题目录不能为空');
        $data['path'] = preg_replace('/^\//','',$data['path']);
        $checkStr= '/^[a-zA-Z0-9_\/]+$/';
        rewrite::regular_back($checkStr,$data['path'],'请正确填专题目录');
        if(isset($_POST['add'])){//增加专题时候检测
            if(file::isDir($data['path']) || $this->model->is_path($data['path'])){
                rewrite::js_back('专题目录已经存在');
            }
        }
        $data['pagenum'] = (int)$data['pagenum'] <= 0 ? 10 : (int)$data['pagenum'];
        return $data;
    }
    
    //删除单个专题
    public function delete(){
        set_time_limit(0);
        $id = (int)$_GET['id'];
        if($id){
            $this->model->delete($id);
        }
        addlog('删除专题【id：'.$id.'】');
        rewrite::succ('删除成功');
    }
    
    
    public function infoManage(){
        if(!$_POST['id']) rewrite::js_back('请选择要移除专题信息');
        $idArr = $_POST['id'];
        if(isset($_POST['removeMore'])){
            $this->removeMore($idArr); //删除多条
        }else if(isset($_POST['remenSub'])){
            $this->model->push('remen',$idArr);//设置热门
            addlog('设置专题信息为热门');
            rewrite::succ('推送成功');
        }else if(isset($_POST['tuijianSub'])){
            $this->model->push('tuijian',$idArr);//设置推荐
            addlog('设置专题信息为推荐');
            rewrite::succ('推送成功');
        }
    }
    
    //管理信息
    public function manageZt(){
        if(isset($_POST['deleteMore'])){
            $this->deleteMoer(); //删除多个专题
        }else if(isset($_POST['sortSub'])){
            $this->model->sort();//排序专题
            addlog('排序专题');
            rewrite::succ('更新排序成功');
        }else if(isset($_POST['updateChangeNum'])){
            if(!$_POST['id']) rewrite::js_back ('请选择要更新数量的专题');
            foreach($_POST['id'] as $v){
                $this->model->updateNumId((int)$v);
            }
            addlog('批量更新专题信息数量');
            rewrite::succ('更新成功');
        }
    }
    
    private function deleteMoer(){
        set_time_limit(0);
        $idArr = $_POST['id'];
        if(!$_POST['id']) rewrite::js_back('请选择要删除的专题');
        foreach($idArr as $v){
            $v = (int)$v;
            if($v){
                $this->model->delete($v);
            }
        }
        addlog('批量删除专题');
        rewrite::succ('删除成功');
    }
    
    
    //更新全部专题信息数量
    public function updateAllNum(){
        $data = $this->model->getData(false);
        foreach($data as $v){
            $this->model->updateNumId($v['id']);
        }
        addlog('更新全部专题信息数量');
        rewrite::succ('更新成功');
    }
    
    //按专题id更新专题信息数量
    public function updateOneNum(){
        $id = (int)$_GET['id'];
        $this->model->updateNumId($id);
        addlog('更新专题信息数量【id：'.$id.'】');
        rewrite::succ('更新成功');
    }
}