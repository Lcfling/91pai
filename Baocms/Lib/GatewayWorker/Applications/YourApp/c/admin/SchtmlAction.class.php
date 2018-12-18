<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台生成静态控制器
 */
defined('LMXCMS') or exit();
class SchtmlAction extends AdminAction{
    private $html = null;
    public function __construct(){
        parent::__construct();
        if($GLOBALS['public']['ishtml'] != 1) rewrite::error('请先开启纯静态模式','?m=Basic&a=index');
        set_time_limit(0); //不限制php执行时间
    }
    
    //生成首页
    public function index(){
        $this->html = new HtmlModel($this->smarty,$this->config);
        $this->html->sc_home();
        unset($this->html);
        rewrite::succ('首页生成成功','?m=Schtml&a=lists');
    }
    //生成全部栏目
    private function sc_allList(){
        if($GLOBALS['allclass']){
            foreach($GLOBALS['allclass'] as $v){
                if($v['classtype'] == 2) continue;
                $this->html->classid_sc($v['classid'],$v);
                rewrite::speed('【'.$v['classname'].'】栏目生成成功');
            }
        }
    }
    
    //按照classid生成栏目
    private function sc_classidaction(){
        if(!$_POST['classid']) rewrite::js_back('请选择栏目');
        foreach($_POST['classid'] as $v){
            if($GLOBALS['allclass'][$v]['classtype'] == 2) continue;
            $this->html->classid_sc($v,$GLOBALS['allclass'][$v]);
            rewrite::speed('【'.$GLOBALS['allclass'][$v]['classname'].'】栏目生成成功');
        }
    }
    
    //get地址生成栏目
    public function getUrlClassid(){
        $_POST['scform'] = true;
        $_POST['classid'] = array((int)$_GET['classid']);
        $_POST['classidsub'] = true;
        $this->lists();
    }
    
    //生成列表页面
    public function lists(){
        if(isset($_POST['scform'])){
            $this->smarty->assign('info','生成栏目中，请勿刷新');
            $this->smarty->display('speed.html');
            $this->html = new HtmlModel($this->smarty,$this->config);
            if(isset($_POST['allclass'])){
                $this->sc_allList();
            }else if(isset($_POST['classidsub'])){
                $this->sc_classidaction();
            }
            rewrite::speedSucc('生成完毕');
            rewrite::speedInfoBack('生成栏目成功');
            unset($this->html);
            exit;
        }
        $this->smarty->assign('classdata',category::classSelect());
        $this->smarty->display('Schtml/list.html');
    }
    
    //生成内容页面
    public function content(){
        if(isset($_POST['scform'])){
            $this->smarty->assign('info','生成内容中，请勿刷新');
            $this->smarty->display('speed.html');
            if(isset($_POST['allsub'])){
                $this->allContent();
            }else if(isset($_POST['idsub'])){
                $this->idContent();
            }else if(isset($_POST['midsub'])){
                $this->midContent();
            }else if(isset($_POST['classidsub'])){
                $this->classidContent();
            }else if(isset($_POST['timesub'])){
                $this->timeContent();
            }
            rewrite::speedSucc('生成完毕');
            rewrite::speedInfoBack('生成栏目成功');
            unset($this->html);
            exit;
        }
        $this->smarty->assign('modData',$GLOBALS['allmodule']);
        $this->smarty->assign('classdata',category::classSelect());
        $this->smarty->display('Schtml/content.html');
    }
    //按照栏目生成内容
    private function classidContent(){
        $classidArr = $_POST['classid'];
        if(!is_array($classidArr)) rewrite::js_back('请选择栏目');
        $this->html = new HtmlModel($this->smarty,$this->config);
        foreach($classidArr as $v){
            if(!isset($GLOBALS['allclass'][$v]) || $GLOBALS['allclass'][$v]['classtype'] != 0) continue; //过滤非普通栏目和不存在的栏目
            $this->html->scInfoHtmlClassid($v);
            rewrite::speed('生成【'.$GLOBALS['allclass'][$v]['classname'].'】栏目全部内容成功');
        }
    }
    
    //按照模型生成内容
    private function midContent(){
        $mid = (int)$_POST['mid'];
        $this->html = new HtmlModel($this->smarty,$this->config);
        $this->html->scMidHtml($mid);
    }
    //生成全部内容
    private function allContent(){
        $this->html = new HtmlModel($this->smarty,$this->config);
        foreach($GLOBALS['allmodule'] as $v){
			$this->html->scMidHtml($v['mid']);
            rewrite::speed('生成【'.$v['mname'].'】模型全部内容成功');
        }
    }
    
    //get地址生成内容
    public function getUrlContent(){
        $_POST['classid'] = (int)$_GET['classid'];
        $_POST['idstar'] = (int)$_GET['id'];
        $_POST['idend'] = (int)$_GET['id'];
        $this->idContent();
        unset($this->html);
        rewrite::succ('生成成功');
    }
    
    //按照内容id生成内容
    private function idContent(){
        $classid = (int)$_POST['classid'];
        if(!isset($GLOBALS['allclass'][$classid]) || $GLOBALS['allclass'][$classid]['classtype'] != 0) return;
        $start = (int)$_POST['idstar'];
        $end = (int)$_POST['idend'];
        if($end < $start || !$start || !$end) rewrite::js_back('请正确填写开始id和结束id');
        //生成条件分组
        $index=1;
        $key = 0;
        for($i=$start;$i<=$end;$i++){
            if($index > $this->config['sc_group_num']){ $key++; $index = 1;} 
            $where[$key][] = $i;
            $index++;
        }
        foreach($where as $v){
            $w[] = implode(',',$v);
        }
        $this->html = new HtmlModel($this->smarty,$this->config);
        $this->html->scInfoIdHtml($w,$classid);
    }
    
    //按照时间生成
    private function timeContent(){
        $classid = (int)$_POST['timeclassid'];
        $time = (int)$_POST['time'];
        if($time <= 0) return;
        $endtime = time() - $time * 3600;
        if($classid == 0){
            foreach($GLOBALS['allclass'] as $v){
                if($v['classtype'] == 0) $classidArr[] = $v['classid'];
            }
        }else{
            $classidArr = array($classid);
        }
        $this->html = new HtmlModel($this->smarty,$this->config);
        $this->html->scInfoTimeHtml($classidArr,$endtime);
    }
    
    //生成专题
    public function sczt(){
        if($_POST['scform']){
            $this->smarty->assign('info','生成专题中，请勿刷新');
            $this->smarty->display('speed.html');
            if(isset($_POST['allzt'])){
                $this->sc_all_zt();
            }else if(isset($_POST['ztid'])){
                $this->sc_id_zt();
            }
            rewrite::speedSucc('生成完毕');
            rewrite::speedInfoBack('生成专题成功');
            unset($this->html);
            exit;
        }
        $this->smarty->display('Schtml/zt.html');
    }
    //get地址生成专题
    public function getUrlZt(){
        $_POST['scform'] = true;
        $_POST['ztid'] = (int)$_GET['id'];
        $this->sczt();
    }
    //按照id生成专题
    private function sc_id_zt(){
        if(!$_POST['ztid']) rewrite::js_back('请选择要生成的专题');
        $ztid = str_replace('，',',',$_POST['ztid']);
        $ztid = explode(',',$ztid);
        $this->html = new HtmlModel($this->smarty,$this->config);
        $ztModel = new ZtModel();
        //开始生成
        foreach($ztid as $v){
            $ztData = $ztModel->getOne($v);
            if(!$ztData) continue;
            $this->html->sc_zt_html($ztData,$ztModel);
            rewrite::speed('专题【'.$ztData['name'].'】生成成功');
        }
    }
    
    
    //生成全部专题
    private function sc_all_zt(){
        $this->html = new HtmlModel($this->smarty,$this->config);
        $ztModel = new ZtModel();
        $ztCoutn = $ztModel->count();
        //分组获取专题数据
        $group_num = 100;
        $group_page = ceil($ztCoutn / $group_num); //获取分组次数
        //循环分组并获取专题数据开始生成
        for($i=0;$i<$group_page;$i++){
            $limit = $i * $group_num .','.$group_num;
            $ztData = $ztModel->getData($limit,false,'id desc');
            //开始按照专题id生成
            foreach($ztData as $v){
                $this->html->sc_zt_html($v,$ztModel);
                rewrite::speed('专题【'.$v['name'].'】生成成功');
            }
        }
    }
    
    //生成Tags
    public function tags(){
        if($_POST['scform']){
            $this->smarty->assign('info','生成Tags中，请勿刷新');
            $this->smarty->display('speed.html');
            $this->html = new HtmlModel($this->smarty,$this->config);
            if($_POST['alltagssub']){
                $this->scAllTags();
            }else if($_POST['tagsidsub']){
                $this->scTagsOne();
            }
            rewrite::speedSucc('生成完毕');
            rewrite::speedInfoBack('生成Tags成功');
            unset($this->html);
            exit;
        }
        $this->smarty->display('Schtml/tags.html');
    }
    
    //生成全部tags
    private function scAllTags(){
        $tagsModel = new TagsModel();
        $count = $tagsModel->tagsCount();
        $group = 50; //每组生成50个tags
        $group_num = ceil($count / $group);
        for($i=0;$i<$group_num;$i++){
            $limit = $i * $group .','.$group;
            $tagsArr = $tagsModel->getData($limit,false,'id asc');
            foreach($tagsArr as $v){
                $this->html->scTags($tagsModel,$v);
            }
            rewrite::speed('生成Tags第 <span class="red">'.($i+1).'</span> 组成功');
        }
    }
    //批量生成tags
    private function scTagsOne(){
        $idStr = $_GET['tagsname'] ? $_GET['tagsname'] : $_POST['tagsname'];
        if(!$idStr) rewrite::js_back('请选择要生成的Tags');
        $tagsModel = new TagsModel();
        $idArr = array_filter(explode(',',$idStr));
        foreach($idArr as $v){
            $tagsData = $tagsModel->getOneName(trim($v),true);
            $this->html->scTags($tagsModel,$tagsData);
            rewrite::speed('生成Tags 【'.$tagsData['name'].'】 成功');
        }
    }
    
    //get地址生成tags
    public function getUrlSctags(){
        $_POST['scform'] = true;
        $_POST['tagsidsub'] = true;
        $this->tags();
    }
}
?>