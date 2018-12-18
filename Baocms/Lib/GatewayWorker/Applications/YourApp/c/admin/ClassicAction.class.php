<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   友情链接控制器
 */
defined('LMXCMS') or exit();
class ClassicAction extends AdminAction{
    private $classicModel = null;
    public function __construct() {
        parent::__construct();
        if($this->classicModel == null) $this->classicModel = new ClassicModel();
    }
    public function index(){
        $where="cid ='".$_GET['cid']."'";
        //eixt();
        $count = $this->classicModel->count($where);

        $page = new page($count,$this->config['page_list_num']);
        $this->smarty->assign('classic',$this->classicModel->getData($page->returnLimit(),$where));
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->assign('id',$_GET['id']);
        $this->smarty->assign('classid',$_GET['classid']);
        $this->smarty->assign('cid',$_GET['cid']);
        $this->smarty->display('Classic/index.html');
    }
    
    //增加友情连接
    public function add(){
        if(isset($_POST['live_id'])){
            $this->classicModel->add($this->check());
            rewrite::succ('增加成功','?m=classic&a=index&id='.$_POST['live_id'].'&classid='.$_POST['classid']);
        }
        $this->smarty->assign('live_id',$_GET['id']);
        $this->smarty->assign('classid',$_GET['classid']);
        $this->smarty->assign('cid',$_GET['cid']);
        $this->smarty->display('Classic/add.html');
    }
    
    //修改友情链接
    public function update(){
        if(isset($_POST['live_id'])){
            $this->classicModel->updateLink($this->check());
            addlog('修改友情链接');
            rewrite::succ('修改成功','?m=Link');
        }
        $data = $this->classicModel->getOne();
        $this->smarty->assign('classic',$data);
        $this->smarty->display('Classic/update.html');
    }
    
    //删除友情链接
    public function delete(){
        $this->linkModel->delete();
        addlog('删除友情链接');
        rewrite::succ('删除成功');
    }
    
    //更新排序
    public function sort(){
        $this->linkModel->sort();
        addlog('排序友情链接');
        rewrite::succ();
    }
    
    //验证数据并返回
    private function check(){
        $data = p(1,1,0,1);
        if($data['live_id']==''){
            rewrite::error('频道id为空');
        }
        if($data['classid']==''){
            rewrite::error('类id为空');
        }
        if($data['cid']==''){
            rewrite::error('cid为空');
        }
        $data['begintime']=strtotime($data['begintime']);
        $data['endtime']=strtotime($data['endtime']);
        return $data;
    }
}
?>