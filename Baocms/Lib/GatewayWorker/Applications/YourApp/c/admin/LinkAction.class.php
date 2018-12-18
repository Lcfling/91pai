<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   友情链接控制器
 */
defined('LMXCMS') or exit();
class LinkAction extends AdminAction{
    private $linkModel = null;
    public function __construct() {
        parent::__construct();
        if($this->linkModel == null) $this->linkModel = new LinkModel();
    }
    
    public function index(){
        $count = $this->linkModel->count();
        $page = new page($count,$this->config['page_list_num']);
        $this->smarty->assign('link',$this->linkModel->getData($page->returnLimit()));
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->display('Link/link.html');
    }
    
    //增加友情连接
    public function add(){
        if(isset($_POST['addLink'])){
            $this->linkModel->add($this->check());
            rewrite::succ('增加成功','?m=Link');
        }
        addlog('增加友情链接');
        $this->smarty->display('Link/addlink.html');
    }
    
    //修改友情链接
    public function update(){
        if(isset($_POST['updateLink'])){
            $this->linkModel->updateLink($this->check());
            addlog('修改友情链接');
            rewrite::succ('修改成功','?m=Link');
        }
        $data = $this->linkModel->getOne();
        $this->smarty->assign('link',$data);
        $this->smarty->display('Link/updatelink.html');
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
        if(!$data['name']) rewrite::js_back('链接名称不能为空');
        if(!$data['url'])  rewrite::js_back('链接地址不能为空');
        rewrite::regular_back('/^http:\/\//',$data['url'],'链接地址请以 http:// 开头');
        $data['isimg'] = $data['img'] ? 1 : 0;
        return $data;
    }
}
?>