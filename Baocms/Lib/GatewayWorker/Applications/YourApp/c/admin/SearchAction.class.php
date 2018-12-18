<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台搜索控制器
 */
defined('LMXCMS') or exit();
class SearchAction extends AdminAction{
    private $searchModel = null;
    public function __construct() {
        parent::__construct();
    }
    
    //后台搜索关键字视图
    public function index(){
        $this->getModel();
        $count = $this->searchModel->count();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->searchModel->lists($page->returnLimit());
        $this->smarty->assign('search_data',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Search/index.html');
    }
    
    //删除搜索关键字
    public function delSearchKey(){
        if(isset($_POST['delmorekey']) || isset($_GET['sid'])){
            //根据id删除
            $this->id_delkey();
        }else if(isset($_POST['delclick'])){
            //根据条件删除
            $this->where_delkey();
        }
        rewrite::succ('删除成功');
    }
    
    //根据id删除关键字
    private function id_delkey(){
        $sid = $_POST['sid'] ? $_POST['sid'] : (int)$_GET['sid'];
        if(!$sid) rewrite::js_back('请选择要删除的关键字');
        $this->getModel();
        if(is_array($sid)){
            $sid = implode(',',$sid);
            $this->searchModel->delKey($sid);
        }else{
            $this->searchModel->delKey($sid);
        }
        addlog('删除搜索关键字【id：'.$sid.'】');
    }
    //根据条件删除关键字
    private function where_delkey(){
        $click = (int)$_POST['click'];
        if($click < 1) rewrite::js_back ('人气数量不能小于1');
        $this->getModel();
        $this->searchModel->delWkey($click);
        addlog('删除搜索关键字【条件：搜索人气小于（'.$click.'）的记录】');
    }
    
    public function getModel(){
        if($this->searchModel == null) $this->searchModel = new SearchModel();
    }
    
    //后台搜索信息列表视图
    public function search(){
        $arr['keywords'] = string::addslashes($_GET['keywords']);
        $arr['field'] =$_GET['field'];
        $arr['mid'] = $_GET['mid'];
        $arr['classid'] = $_GET['classid'];
        if(!$arr['mid'] && !$arr['classid']) rewrite::js_back('必须选择模型或者栏目');
        $arr['attr'] = $_GET['attr'];
        $arr['state'] = $_GET['state'];
        $arr['time'] = $_GET['time'];
        $arr['state'] = $_GET['state'];
        switch($arr['time']){
            case 3 : $arr['time'] = 7;break;
            case 4 : $arr['time'] = 30;break;
            case 5 : $arr['time'] = 90;break;
            case 6 : $arr['time'] = 180;break;
            case 7 : $arr['time'] = 365;break;
        }
        $this->getModel();
        //初始化条件
        $arr = $this->searchModel->getSerachField($arr);
        //获取总条数
        $num = $this->searchModel->searchCoutn($arr);
        
        //获取分页
        $page = new page($num,$this->config['page_list_num']);
        //获取数据
        $arr['page'] = $page->returnLimit();
        $list = $this->searchModel->getSearchList($arr);
        //赋值url
        if($list){
            foreach($list as $v){
                $param['type'] = 'content';
                $param['classid'] = $v['classid'];
                $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                $param['time'] = $v['time'];
                $param['id'] = $v['id'];
                $v['url'] = $v['url'] ? $v['url'] : url($param);
                $newlist[] = $v;
            }
        }
        //加入搜索下拉选择默认
        $this->smarty->assign('attr',$arr['attr']);
        $this->smarty->assign('time',$_GET['time']);
        $this->smarty->assign('keywords',$_GET['keywords']);
        
        
        $this->smarty->assign('num',$num);
        $this->smarty->assign('listInfo',$newlist);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('classData',$GLOBALS['allclass'][$arr['classid']]);
        $this->smarty->assign('allModData',$GLOBALS['allmodule']);
        $this->smarty->assign('modData',$GLOBALS['allmodule'][$arr['mid']]);
        $this->smarty->assign('tuijianSelect',formatHot($GLOBALS['public']['tuijianSelect']));
        $this->smarty->assign('remenSelect',formatHot($GLOBALS['public']['remenSelect']));
        $this->smarty->assign('selectData',category::classSelect(true));
        $this->smarty->display('Content/content.html');
    }
    
}
?>
