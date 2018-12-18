<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   友情链接控制器
 */
defined('LMXCMS') or exit();
class OrderAction extends AdminAction{
    private $orderModel = null;
    public function __construct() {
        parent::__construct();
        if($this->orderModel == null) $this->orderModel = new OrderModel();
    }
    
    public function index(){
        $count = $this->orderModel->count();
        $page = new page($count,$this->config['page_list_num']);
        $orderdata=$this->orderModel->getlist($page->returnLimit());

        $usermodel= new UserModel();
        $userdata=$usermodel->getAllData();//所有用户信息
        $cmodel=new ContentModel(6);

        //$c_date=$cmodel->getallInfolist(6);
        //die("a");
        //print_r($c_date);
        foreach ($orderdata as $k => $v){
            foreach ($userdata as $t){
                if($v['uid']==$t['id']){
                    $orderdata[$k]['user_data']=$t;
                }
            }

        }
        $this->smarty->assign('order',$orderdata);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->display('Order/index.html');
    }
    
    public function send(){
        $orderid=$_GET['id'];
        $data['fh_state']=1;
        $this->orderModel->set_fh_state($orderid,$data);
        rewrite::succ("发货成功");
    }
}
?>