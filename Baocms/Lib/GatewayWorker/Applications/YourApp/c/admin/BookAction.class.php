<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   留言控制器
 */
defined('LMXCMS') or exit();
class BookAction extends AdminAction{
    private $bookModel = null;
    public function __construct() {
        parent::__construct();
        if($this->bookModel == null) $this->bookModel = new BookModel();
    }
    
    public function index(){
        $count = $this->bookModel->count();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->bookModel->getData($page->returnLimit());
        $this->smarty->assign('book',$data);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->display('Book/book.html');
    }
    
    //审核和取消审核
    public function check(){
        $this->bookModel->ischeck();
        addlog('审核留言【id：'.$_GET['id'].'】');
        rewrite::succ();
    }
    
    //删除留言
    public function delete(){
        $this->bookModel->delete();
        addlog('删除留言【id：'.$_GET['id'].'】');
        rewrite::succ('删除成功');
    }
    
    //回复留言
    public function reply(){
        $id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
        //获取回复数据
        $reply = $this->bookModel->getReply(array($id));
        if($reply){
            $reply = string::html_char($reply[0]['content']);
            $this->smarty->assign('content',$reply);
            $this->smarty->assign('type','update');
        }else{
            $this->smarty->assign('type','add');
        }
        if(isset($_POST['reply'])){
            if(!$_POST['content']){
                rewrite::js_back('回复内容不能为空');
            }
            $this->bookModel->reply(array('id'=>$id,'type'=>$_POST['type'],'username'=>$this->username));
            addlog('留言回复【id：'.$_POST['id'].'】');
            rewrite::succ('修改成功','?m=Book');
        }
        $this->smarty->assign('id',$id);
        $this->smarty->display('Book/reply.html');
    }
}
?>