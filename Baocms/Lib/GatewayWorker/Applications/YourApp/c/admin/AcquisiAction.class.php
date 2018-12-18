<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   采集控制器
 */
defined('LMXCMS') or exit();
class AcquisiAction extends AdminAction{
    private $model = null;
    private $fieldCj; //节点字段
    private $fieldData; //数据字段
    private $fieldList; //采集字段
    private $curl;
    private $id;
    private $uid;
    private $lid;
    public function __construct() {
        parent::__construct();
        session_write_close(); //防止session阻塞
        if(!function_exists('curl_init')) rewrite::error('空间不支持【curl】功能，无法采集，请联系你的空间商','?m=Index&a=main',3000);
        if($this->model == null) $this->model = new AcquisiModel();
        $this->id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
        $this->uid = $_GET['uid'] ? $_GET['uid'] : $_POST['uid'];
        $this->lid = $_GET['lid'] ? $_GET['lid'] : $_POST['lid'];
        $this->fieldCj = array('name','mid','content');
    }
    
    //列表
    public function index(){
        $count = $this->model->count();
        $page = new page($count,$this->config['page_list_num']);
        $data = $this->model->getData($page->returnLimit());
        $this->smarty->assign('list',$data);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->display('Caiji/index.html');
    }
    
    //创建节点
    public function add(){
        if(isset($_POST['add'])){
            $data = d($this->fieldCj,p(1),array('mid'));
            if(!$data['name']) rewrite::js_back('节点名字不能为空');
            $this->model->add($data);
            addlog('创建采集节点');
            rewrite::succ('创建成功',$_POST['backurl']);
        }
        $this->smarty->display('Caiji/add.html');
    }
    
    //修改节点
    public function update(){
        if(isset($_POST['update'])){
            $id = $this->id;
            $data = d($this->fieldCj,p(1),array('mid'));
            if(!$data['name']) rewrite::js_back('节点名字不能为空');
            unset($data['mid']);
            $this->model->update($data,$id);
            addlog('修改采集节点【id：'.$id.'】');
            rewrite::succ('修改成功',$_POST['backurl']);
        }
        $data = $this->model->getOne($this->id);
        $this->smarty->assign($data);
        $this->smarty->display('Caiji/update.html');
    }
    
    //删除节点
    public function delete(){
        //查询采集id
        $cjId = $this->model->jd2cjId($this->id);
        if($cjId){
            foreach($cjId as $v){
                $this->model->delete_cjid_data($v['lid']); //删除该规则下的数据
                $this->model->delete_list_url($v['lid']); //清空网址库
            }
            //根据节点id删除所属采集规则
            $this->model->delete_cj_list($this->id);
        }
        //删除节点
        $this->model->delete_jd_data($this->id);
        addlog('删除节点以及节点采集规则和网址库和规则数据');
        rewrite::succ('删除成功');
    }
    
    //根据lid清空网址库
    public function delete_list_url(){
        $this->model->delete_list_url($this->lid);
        addlog('清空【采集id：'.$this->lid.'】采集下的已采集网址库');
        rewrite::succ('清除成功');
    }
    
    //根据采集id删除采集规则和采集数据
    public function deleteRegular(){
        $this->model->delete_cjid_data($this->lid);
        $this->model->delete_cj_regular($this->lid);
        $this->model->delete_list_url($this->lid);
        addlog('删除采集规则和采集数据和网址库');
        rewrite::succ('删除成功');
    }
    
    //管理节点
    public function manage(){
        $jdData = $this->model->getOne($this->id);
        $count = $this->model->cj_count($this->id);
        $page = new page($count,$this->config['page_list_num']);
        $list = $this->model->cj_list($this->id,$page->returnLimit());
        $this->smarty->assign('list',$list);
        $this->smarty->assign('num',$count);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('jdData',$jdData);
        $this->smarty->display('Caiji/list.html');
    }
    
    //创建采集规则视图
    public function addCaiji(){
        $jdData = $this->model->getOne($this->id);
        $this->smarty->assign('jdData',$jdData);
        $this->smarty->assign('fieldArr',$GLOBALS['allfield'][$jdData['mid']]);
        $this->smarty->display('Caiji/addCaiji.html');
    }
    
    //修改采集规则视图
    public function updateCaiji(){
        $jdData = $this->model->getOne($this->id);
        $this->smarty->assign('jdData',$jdData);
        $this->smarty->assign('fieldArr',$GLOBALS['allfield'][$jdData['mid']]);
        $data = $this->model->getOneCjData($this->lid);
        foreach($data['array'] as $k=>$v){
            $v['regular'] = stripslashes($v['regular']);
            $data['array'][$k] = $v;
        }
        $this->smarty->assign($data);
        $this->smarty->display('Caiji/updateCaiji.html');
    }
    //根据采集数据id从新采集
    public function againCaiji(){
        $this->smarty->assign('info','正在重新采集，请勿刷新');
        $this->smarty->display('speed.html');
        $regularData = $this->model->caijiData($this->lid);
        $caijiData = $this->model->caijiDataOne($this->id);
        $caiji = new caiji($regularData);
        rewrite::speed('正在重新采集，请稍后...');
        $content_data = $caiji->StartCj($caijiData['url']);
        $content_data = var_export($content_data,true);
        $this->model->updateCaijiData($this->id,$content_data);
        rewrite::speedSucc('重新采集成功');
        rewrite::speedInfoBack('重新采集成功，请返回查看');
    }
    
    
    //修改采集规则
    public function updateCaijiData(){
        $data = $this->checkData();
        unset($data['uid']);
        $this->model->updateCjData($this->lid,$data);
        addlog('修改采集规则【id：'.$this->lid.'】');
        rewrite::succ('修改成功',$_POST['backurl']);
    }
    
    //增加采集规则
    public function saveCaiji(){
        $data = $this->checkData();
        $this->model->addCj($data);
        addlog('创建采集');
        rewrite::succ('创建采集成功',$_POST['backurl']);
    }
    
    //根据id和classid入库一条采集数据
    public function one_inMysql(){
        set_time_limit(0);
        $id = $_GET['id'];
        $classid = $_GET['classid'];
        $curl = new curl();
        $this->smarty->assign('info','数据正在入库中，请勿刷新');
        $this->smarty->display('speed.html');
        $this->model->in_mysql_one($curl,$id,$classid);
        rewrite::speedSucc('入库成功');
        rewrite::speedInfoBack('数据全部入库完成');
        addlog('采集数据入库【id：'.$id.'，classid：'.$classid.'】');
    }
    //采集数据管理
    public function manageCaijiData(){
        if(isset($_POST['more_inMysql'])){
            //批量入库
            $this->more_inMysql();
        }else if(isset($_POST['more_del_cjData'])){
            //批量删除
            $this->delete_caiji_data();
        }else if(isset($_POST['del_in_mysql'])){
            //删除已入库数据
            $this->delete_is_inmysql();
        }
    }
    //删除已入库的采集数据
    public function delete_is_inmysql(){
        $this->model->delete_is_inMysql();
        addlog('删除已入库采集数据');
        rewrite::succ('删除成功');
    }
    //批量删除数据
    public function delete_caiji_data(){
        $idArr = $_POST['id'];
        if(!$idArr) rewrite::js_back('请选择删除的数据');
        $this->model->more_delete_cjdata($idArr);
        addlog('批量删除采集数据');
        rewrite::succ('删除成功');
    }
    //入库全部未入库的信息
    public function inMysql(){
        set_time_limit(0);
        $this->smarty->assign('info','数据正在入库中，请勿刷新');
        $this->smarty->display('speed.html');
        $regularData = $this->model->getOneCjData($this->lid);
        $classid = $regularData['classid'];
        $curl = new curl();
        $count = $this->model->not_inMysql_count($this->lid);
        $group_num = 10; //每组导入数量
        $group_page = ceil($count / $group_num); //总共多少组
        //循环导入
        for($i=0;$i<$group_page;$i++){
            $limit = $i * $group_num.','.$group_num;
            $idArr = $this->model->not_inMysql_id($limit,$this->lid);
            //根据id遍历导入
            foreach($idArr as $v){
				$this->model->in_mysql_one($curl,$v['id'],$classid);
            }
        }
        addlog('入库【采集id：'.$this->lid.'】全部采集数据');
        rewrite::speedSucc('入库成功');
        rewrite::speedInfoBack('数据全部入库完成');
    }
    //批量入库
    public function more_inMysql(){
        set_time_limit(0);
        $idArr = $_POST['id'];
        $classid = $_POST['classid'];
        if(!$idArr) rewrite::js_back('请选择要入库的数据');
        $this->smarty->assign('info','数据正在入库中，请勿刷新');
        $this->smarty->display('speed.html');
        $curl = new curl();
        foreach($idArr as $v){
			$this->model->in_mysql_one($curl,$v,$classid);
        }
        rewrite::speedSucc('入库成功');
        rewrite::speedInfoBack('数据全部入库完成');
        addlog('批量入库采集数据');
    }
    
    //返回验证数据
    private function checkData(){
        $data = p(1,1);
        if(!$data['lname']) rewrite::js_back('采集名称不能为空');
        if(!(int)$data['classid']) rewrite::js_back('请选择入库栏目');
        $arr['classid'] = (int)$data['classid'];
        $arr['lname'] = $data['lname'];
        if(!(int)$data['uid']) rewrite::js_back('节点不存在');
        $arr['uid'] = (int)$data['uid'];
        $arr['list_dy_url'] = $data['list_dy_url'];
        $urlArr = array();
        if($data['list_dy_url']){
            foreach(explode("\n",$data['list_dy_url']) as $v){
                $urlArr[] = urldecode($v);
            }
        }
        $arr['list_url_tem'] = urldecode($data['list_url_tem']);
        $arr['pre_page'] = $data['pre_page'];
        $arr['fix_page'] = $data['fix_page'];
        $arr['start_page'] = (int)$data['start_page'];
        $arr['end_page'] = (int)$data['end_page'];
        $arr['jg_page'] = (int)$data['jg_page'];
        $arr['desc_page'] = $data['desc_page'] ? 1 : 0;
        $arr['remove_page_fix'] = $data['remove_page_fix'] ? 1 : 0;
        if($arr['list_url_tem']){
            $param['list_url_tem'] = $arr['list_url_tem'];
            $param['pre_page'] = $arr['pre_page'];
            $param['fix_page'] = $arr['fix_page'];
            $param['start_page'] = $arr['start_page'];
            $param['end_page'] = $arr['end_page'];
            $param['jg_page'] = $arr['jg_page'];
            $param['desc_page'] = $arr['desc_page'];
            $param['remove_page_fix'] = $arr['remove_page_fix'];
            $list_url_arr = $this->sc_list_url($param);
            foreach($list_url_arr as $v){
                $urlArr[] = urldecode($v);
            }
        }
        $arr['url_str'] = implode('#####',$urlArr);
        if(!$arr['url_str']) rewrite::js_back('目标列表地址不能为空');
        if(!$data['content_url_box']) rewrite::js_back('目标列表页面中内容链接地址区域正则不能为空');
        $arr['content_url_box'] = $data['content_url_box'];
        if(!$data['content_url_regular']) rewrite::js_back('目标列表页面中内容页面地址正则不能为空');
        $arr['content_url_regular'] = $data['content_url_regular'];
        $arr['lcontent'] = $data['lcontent'];
        $arr['num'] = (int)$data['num'];
        $arr['is_fenci_tags'] = (int)$data['is_fenci_tags'] ? 1 : 0;
        $arr['tagsname'] = $data['tagsname'] ? str_replace('，',',',$data['tagsname']) : '';
        $arr['ztid'] = str_replace('，',',',$data['ztid']);
        $arr['str_y_replace'] = str_replace('，',',',$data['str_y_replace']);
        $arr['str_n_replace'] = str_replace('，',',',$data['str_n_replace']);
        $arr['str_remove'] = $data['str_remove'];
        $arr['remove_html'] = $data['remove_html'] ? addslashes(var_export($data['remove_html'],true)) : '';
        $arr['is_info_page'] = (int)$data['is_info_page'];
        $arr['info_page_regular'] = $arr['is_info_page'] ? $data['info_page_regular'] : '';
        $arr['time_jg'] = (int)$data['time_jg'] ? (int)$data['time_jg'] : 0;
        $arr['array'] = addslashes(var_export($data['fieldData'],true));
        return $arr;
    }
    
    //修改采集数据
    public function showCjData(){
        $cjData = $this->model->getOneCjData($this->lid);
        $jdData = $this->model->getOne($this->id);
        $fieldData = $GLOBALS['allfield'][$jdData['mid']];
        $temdata = $this->model->caijiDataOne($_GET['cid']);
        $this->smarty->assign('jdData',$jdData);
        $this->smarty->assign('cjData',$cjData);
        $fieldData = tool::arrV2K($fieldData,'fname');
        eval('$data = '.$temdata['data'].';');
        //格式化数据
        foreach($data as $k=>$v){
            if($fieldData[$k]['ftype'] == 'moreimage' || $fieldData[$k]['ftype'] == 'morefile'){
                $v = explode('#####',$v);
            }
            if(isset($fieldData[$k])){
                $newData[$k]['name'] = $fieldData[$k]['ftitle'];
            }else{
                switch($k){
                    case 'title' : $newData[$k]['name'] = '标题'; break;
                    case 'keywords' : $newData[$k]['name'] = '网页关键字'; break;
                    case 'description' : $newData[$k]['name'] = '网页描述'; break;
                    case 'tagsname' : $newData[$k]['name'] = '增加到Tags'; break;
                    case 'ztid' : $newData[$k]['name'] = '增加到专题id'; break;
                }
            }
            if($k == 'title' ||$k == 'keywords' ||$k == 'tagsname' ||$k == 'ztid'){
                $newData[$k]['type'] = 'text';
            }else if($k == 'description'){
                $newData[$k]['type'] = 'textarea';
            }else{
                $newData[$k]['type'] = $fieldData[$k]['ftype'];
            }
            $newData[$k]['value'] = $v;
        }
        $this->smarty->assign('url',$temdata['url']);
        $this->smarty->assign('id',$temdata['id']);
        $this->smarty->assign('data',$newData);
        $this->smarty->display('Caiji/showData.html');
    }
    
    //修改保存采集数据
    public function updateCjtoData(){
        $id = $_POST['id'];
        $backurl = $_POST['backurl'];
        unset($_POST['id'],$_POST['backurl'],$_POST['updateCaiji']);
        foreach($_POST as $k=>$v){
            if(is_array($v)){
                $v = implode('#####',$v);
            }
            $newData[$k] = $v;
        }
        $newData['keywords'] = str_replace('，',',',$newData['keywords']);
        $newData['tagsname'] = str_replace('，',',',$newData['tagsname']);
        $newData['ztid'] = str_replace('，',',',$newData['ztid']);
        $newData['keywords'] = preg_replace('/[,]+/',',',$newData['keywords']);
        $newData['tagsname'] = preg_replace('/[,]+/',',',$newData['tagsname']);
        $newData['ztid'] = preg_replace('/[,]+/',',',$newData['ztid']);
        $data = var_export($newData,true);
        $this->model->updateCaijiData($id,$data);
        addlog('修改采集数据【id：'.$id.'】');
        rewrite::succ('修改成功',$backurl);
    }
    
    //查看采集数据列表
    public function caijiDataList(){
        $cjData = $this->model->getOneCjData($this->lid);
        $jdData = $this->model->getOne($this->id);
        $count = $this->model->caijiDataCount($this->lid);
        $page = new page($count,30);
        $listData = $this->model->caijiDataList($page->returnLimit(),$this->lid);
        foreach($listData as $v){
            if(trim($v['data'])){
                eval('$v[\'data\'] = '.$v['data'].';');
            }
            $newData[] = $v;
        }
        $this->smarty->assign('jdData',$jdData);
        $this->smarty->assign('cjData',$cjData);
        $this->smarty->assign('list',$newData);
        $this->smarty->assign('page',$page->html());
        $this->smarty->assign('num',$count);
        $this->smarty->display('Caiji/datalist.html');
    }
    
    //删除采集数据
    public function deleteCjData(){
        $this->model->delete_cj_data($this->id);
        addlog('删除采集数据【id：'.$this->id.'】');
        rewrite::succ('删除成功');
    }
    
    
    
    //开始采集
    public function startCaiji(){
        //设置php执行时间
        set_time_limit(0);
        $caijiRegularData = $this->model->caijiData($this->lid);
        $caiji = new caiji($caijiRegularData);
        $this->smarty->assign('info','数据采集中');
        $this->smarty->display('speed.html');
        $index = 0;
        $i = 0;
        //遍历所有列表地址
        foreach(explode("#####",$caijiRegularData['url_str']) as $v){
            $content_url = $caiji->caiji_list_content($v);//进入列表页面获取内容地址
            if($content_url['succ']){
                //遍历列表中获取的内容页面
                if(!$content_url['content']) continue;
                foreach($content_url['content'] as $url){
                    $index++;
                    //检测该地址是否已经采集
                    if($this->model->is_caiji_url($url)) continue;
                    if($caijiRegularData['num'] > 0 && $index > $caijiRegularData['num']) break; //限制采集数量
                    $data = $caiji->StartCj($url);
                    //采集到的数据添加到数据库
                    if(trim($data['title'])){
                        $caijiData['data'] = $data;
                        $caijiData['uid'] = $this->uid;
                        $caijiData['lid'] = $this->lid;
                        $caijiData['url'] = $url;
                        $this->model->addCaijiData($caijiData);
                        //增加该地址到地址库
                        $this->model->addContentUrl($url,$this->lid);
                        rewrite::speed('采集第<span class="hong"> '.($i+1).'</span> 条成功');
                        $i++;
                    }else{
                        rewrite::speed('采集【'.$url.'】失败，可能原因【链接超时或失败】请稍后再次采集尝试');
                    }
                    if($caijiRegularData['time_jg']){
                        rewrite::speed('间隔时间等待中...');
                        sleep($caijiRegularData['time_jg']);
                    }
                }
            }else{
                rewrite::speed('进入列表页面失败【'.$v.'】【'.$content_url['content'].'】，继续下一条');
            }
        }
        rewrite::speedSucc('采集成功');
        rewrite::speedInfoBack('采集成功，如果有没有成功的，请直接F5刷新重新采集即可，因为有时候可能网络链接失败');
        addlog('采集数据【采集id：'.$this->lid.'】');
    }
    
    //采集测试
    public function testcj(){
        $caijiData = $this->model->caijiData($this->lid);
        $caiji = new caiji($caijiData);
        $Fielddata = $caiji->testCaiji();
        $field = $GLOBALS['allfield'][$caijiData['mid']];
        $list[0]['name'] = '标题';
        $list[0]['value'] = $Fielddata['title'];
        $list[1]['name'] = '网页关键字';
        $list[1]['value'] = $Fielddata['keywords'];
        $list[2]['name'] = '网页描述';
        $list[2]['value'] = $Fielddata['description'];
        $index = 3;
        foreach($field as $v){
            if($v['ftype'] == 'moreimage' || $v['ftype'] == 'morefile'){
                $list[$index]['name'] = $v['ftitle'];
                $list[$index]['value'] = explode('#####',$Fielddata[$v['fname']]);
            }else{
                $list[$index]['name'] = $v['ftitle'];
                $list[$index]['value'] = $Fielddata[$v['fname']];
            }
            $index++;
        }
        $list[100]['name'] = '增加Tags';
        $list[100]['value'] = $Fielddata['tagsname'];
        $list[101]['name'] = '增加专题id';
        $list[101]['value'] = $Fielddata['ztid'];
        $this->smarty->assign('caijiId',$caijiData['id']);
        $this->smarty->assign('caijiName',$caijiData['name']);
        $this->smarty->assign('list',$list);
        $this->smarty->display('Caiji/testCaiji.html');
    }
    
    //测试目标列表地址
    public function test_list_url(){
        $_GET['list_url_tem'] = urldecode($_GET['list_url_tem']);
        $_GET['pre_page'] = urldecode($_GET['pre_page']);
        $_GET['fix_page'] = urldecode($_GET['fix_page']);
        foreach($_GET as $k => $v){
            $param[$k] = $v;
        }
        $urlArr = $this->sc_list_url($param);
        $this->smarty->assign('list',$urlArr);
        $this->smarty->display('Caiji/testurl.html');
    }
    
    //测试提取目标列表页面内容地址
    public function testListContentUrl(){
        $listurl = urldecode($_GET['listurl']);
        $urlpre = getDomain($listurl);
        $param['content_url_box'] = urldecode($_GET['content_url_box']);
        $param['content_url_regular'] = urldecode($_GET['content_url_regular']);
        $caiji = new caiji();
        $urlArr = $caiji->getContentUrl($listurl,$urlpre,$param);
        $this->smarty->assign('list',$urlArr['content']);
        $this->smarty->display('Caiji/testurl.html');
    }
    
    //测试获取内容页面分页页码
    public function testContentPage(){
        $url = urldecode($_GET['contenturl']);
        $param['info_page_regular'] = urldecode($_GET['info_page_regular']);
        $urlpre = getDomain($url);
        $caiji = new caiji();
        //递归获取内容页码
        $data = $caiji->forContentPage(array('content'=>array($url)),$urlpre,$param);
        $this->smarty->assign('list',$data);
        $this->smarty->display('Caiji/testurl.html');
    }
    
    
    
    //系统生成目标列表地址
    private function sc_list_url($param){
        //$param['list_url_tem'] 地址模板
        //$param['start_page'] 开始页码
        //$param['end_page'] 结束页码
        //$param['jg_page'] 间隔倍数
        //$param['desc_page'] 是否倒序
        //$param['remove_page_fix'] 第一页是否删除后缀
        //$param['pre_page'] 页码前面加字符
        //$param['fix_page'] 页码后面加字符
        $urlArr = array();
        $temurl = trim($param['list_url_tem']);
        $start_page = (int)$param['start_page'];
        $end_page = (int)$param['end_page'];
        $jg_page = (int)$param['jg_page'];
        if($start_page==0){
		    if(!$temurl || !$end_page || !$jg_page) return $urlArr;
		}else{
		    if(!$temurl || !$end_page || !$jg_page || !$start_page) return $urlArr;
		}
        $desc_page = $param['desc_page'] ? 1 : 0;
        $remove_page_fix = $param['remove_page_fix'] ? 1 : 0;
        $pre_page = $param['pre_page'] ? $param['pre_page'] : '';
        $fix_page = $param['fix_page'] ? $param['fix_page'] : '';
        $j = $start_page; //记录初始值
        if($desc_page){//倒序
            if($start_page < $end_page) return $urlArr;
            while($start_page >= $end_page){
                if($remove_page_fix && $start_page == $j){
                    $urlArr[] = str_replace('[page]','',$temurl);
                }else{
                    $urlArr[] = str_replace('[page]',$pre_page.$start_page.$fix_page,$temurl);
                }
                $start_page = $start_page - $jg_page;
            }
        }else{//正序
            if($start_page > $end_page) return $urlArr;
            while($start_page <= $end_page){
                if($remove_page_fix && $start_page == $j){
                    $urlArr[] = str_replace('[page]','',$temurl);
                }else{
                    $urlArr[] = str_replace('[page]',$pre_page.$start_page.$fix_page,$temurl);
                }
                $start_page = $start_page + $jg_page;
            }
        }
        return $urlArr;        
    }
    
    
}
?>