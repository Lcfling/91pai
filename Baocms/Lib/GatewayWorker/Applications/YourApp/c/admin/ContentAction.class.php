<?php 

/**

 *  【梦想cms】 http://www.lmxcms.com

 * 

 *   后台首页控制器

 */

defined('LMXCMS') or exit();

class IndexAction extends AdminAction{

    private $contentModel = null;

    private $fieldArr;

    private $classid;

    private $id;

    public function __construct() {

        parent::__construct();

        $this->classid = (int)$_POST['classid'] ? (int)$_POST['classid'] : (int)$_GET['classid'];

        $this->id = (int)$_POST['id'] ? (int)$_POST['id'] : (int)$_GET['id'];

        if(!isset($GLOBALS['allclass'][$this->classid])) rewrite::js_back('栏目不存在');

        if(!isset($GLOBALS['allmodule'][$GLOBALS['allclass'][$this->classid]['mid']])) rewrite::js_back('该栏目所属模型不存在');

        //if($this->contentModel == null) $this->contentModel = new ContentModel($this->classid);

        // 动态模型临时解决办法 @lengsad

        if($this->contentModel == null) $this->contentModel = $GLOBALS['allclass'][$this->classid]['mid']== 5 ? new LiveContentModel($this->classid) : new ContentModel($this->classid);

    }

    //修改

    public function update(){

        $this->fieldArr = $GLOBALS['allfield'][$GLOBALS['allclass'][$this->classid]['mid']];

        //修改提交

        if(isset($_POST['updateInfo'])){

            $this->contentModel->updateInfo($this->check());

            addlog('修改信息【classid：'.$this->classid.'】【id：'.$this->id.'】');

            rewrite::succ('修改成功',$_POST['backurl']);

        }

        //获取数据

        $data = $this->contentModel->updateData($this->id);

        if(!$data)rewrite::js_back('信息不存在');

        $mid = $GLOBALS['allclass'][$this->classid]['mid'];

        foreach($this->fieldArr as $v){

            if($v['ftype'] == 'editor'){

                //插入编辑器所需要的js文件

                $this->smarty->assign('editorFileJs',editJs());

                break;

            }

        }

        //注入变量

        foreach($data as $k => $v){

            $this->smarty->assign($k,is_string($v) ? string::html_char($v) : $v);

        }

        $this->smarty->assign('tuijianSelect',formatHot($GLOBALS['public']['tuijianSelect']));

        $this->smarty->assign('remenSelect',formatHot($GLOBALS['public']['remenSelect']));

        $this->smarty->assign('classData',$GLOBALS['allclass'][$this->classid]);

		$this->smarty->assign('mid',$GLOBALS['allclass'][$this->classid]['mid']);

		$this->smarty->assign('allclass',$GLOBALS['allclass']);

        $this->smarty->assign('classid',$this->classid);

        $this->smarty->assign('id',$this->id);

        $this->smarty->assign('update',true);

        $this->smarty->assign('formdir',ROOT_PATH.'data/form/'.$mid.'.php');

        $this->smarty->display('Content/updatecontent.html');

    }

	//复制

    public function copyInfo(){

        $this->fieldArr = $GLOBALS['allfield'][$GLOBALS['allclass'][$this->classid]['mid']];

        //修改提交

        if(isset($_POST['updateInfo'])){

            $this->contentModel->copyInfo($this->check());

            addlog('复制信息【classid：'.$this->classid.'】【id：'.$this->id.'】');

            rewrite::succ('复制成功',$_POST['backurl']);

        }

        //获取数据

        $data = $this->contentModel->updateData($this->id);

        if(!$data)rewrite::js_back('信息不存在');

        $mid = $GLOBALS['allclass'][$this->classid]['mid'];

        foreach($this->fieldArr as $v){

            if($v['ftype'] == 'editor'){

                //插入编辑器所需要的js文件

                $this->smarty->assign('editorFileJs',editJs());

                break;

            }

        }

        //注入变量

        foreach($data as $k => $v){

            $this->smarty->assign($k,is_string($v) ? string::html_char($v) : $v);

        }

        $this->smarty->assign('tuijianSelect',formatHot($GLOBALS['public']['tuijianSelect']));

        $this->smarty->assign('remenSelect',formatHot($GLOBALS['public']['remenSelect']));

        $this->smarty->assign('classData',$GLOBALS['allclass'][$this->classid]);

		$this->smarty->assign('mid',$GLOBALS['allclass'][$this->classid]['mid']);

		$this->smarty->assign('allclass',$GLOBALS['allclass']);

        $this->smarty->assign('classid',$this->classid);

        $this->smarty->assign('id',$this->id);

        $this->smarty->assign('update',true);

        $this->smarty->assign('formdir',ROOT_PATH.'data/form/'.$mid.'.php');

        $this->smarty->display('Content/copycontent.html');

    }

    //获取内容列表视图数据并注入变量

    private function InfolistVar(){

        //获取总条数

        $num = $this->contentModel->count($this->classid);

        //获取分页

        $page = new page($num,$this->config['page_list_num']);

        //获取信息列表

        $listInfo = $this->contentModel->getInfolist($page->returnLimit(),$this->classid);

        //赋值url

        if($listInfo){

            foreach($listInfo as $v){

                $param['type'] = 'content';

                $param['classid'] = $v['classid'];

                $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];

                $param['time'] = $v['time'];

                $param['id'] = $v['id'];

                $v['url'] = $v['url'] ? $v['url'] : url($param);

                $newlist[] = $v;

            }

        }

        $this->smarty->assign('num',$num);

        $this->smarty->assign('listInfo',$newlist);

        $this->smarty->assign('page',$page->html());

    }

    //内容列表视图

    public function index(){

        $this->InfolistVar();

        $this->smarty->assign('allModData',$GLOBALS['allmodule']);

        $this->smarty->assign('tuijianSelect',formatHot($GLOBALS['public']['tuijianSelect']));

        $this->smarty->assign('remenSelect',formatHot($GLOBALS['public']['remenSelect']));

        $this->smarty->assign('modData',$GLOBALS['allmodule'][$GLOBALS['allclass'][$this->classid]['mid']]);

        $this->smarty->assign('classData',$GLOBALS['allclass'][$this->classid]);

        $this->smarty->assign('selectData',category::classSelect(0));

        $this->smarty->display('Content/content.html');

    }

    

    //信息操作管理

    public function infoManage(){

        if(!$_POST['id']) rewrite::js_back('请选择信息');

        if(isset($_POST['deleteInfo'])){

            $this->deleteMore();

        }else if(isset($_POST['tuijianInfo'])){

            $this->tuijianInfo();

        }else if(isset($_POST['remenInfo'])){

            $this->remenInfo();

        }

    }

    

    //批量删除信息

    private function deleteMore(){

        foreach($_POST['id'] as $v){

            $this->contentModel->delete($v);

        }

        addlog('批量删除信息【classid：'.$this->classid.'】【id：'.implode('、',$_POST['id']).'】');

        rewrite::succ('删除成功');

    }

    

    //删除信息控制

    public function delete(){

        $this->contentModel->delete($this->id);

        addlog('删除信息【classid：'.$this->classid.'】【id：'.$this->id.'】');

        rewrite::succ('删除成功');

    }

    

    //推荐信息

    private function tuijianInfo(){

        foreach($_POST['id'] as $v){

            $idArr[] = (int)$v;

        }

        $this->contentModel->tuijian($idArr);

        rewrite::succ();

    }

    

    //热门信息

    private function remenInfo(){

        foreach($_POST['id'] as $v){

            $idArr[] = (int)$v;

        }

        $this->contentModel->remen($idArr);

        rewrite::succ();

    }

    

    //增加信息

    public function add(){

        $this->fieldArr = $GLOBALS['allfield'][$GLOBALS['allclass'][$this->classid]['mid']];

        if(isset($_POST['addInfo'])){

            //增加信息表单接收

            $addData = $this->check();

            $addData[1]['click'] = rand($this->config['clickMax'][0],$this->config['clickMax'][1]);

            $this->contentModel->add($addData);

            addlog('增加信息【classid：'.$this->classid.'】');

            rewrite::succ('增加信息成功','?m=Content&a=index&classid='.$this->classid);

        }

        $mid = $GLOBALS['allclass'][$this->classid]['mid'];

        foreach($this->fieldArr as $v){

            if($v['ftype'] == 'editor'){

                //插入编辑器所需要的js文件

                $this->smarty->assign('editorFileJs',editJs());

                break;

            }

        }

        $this->smarty->assign('tuijianSelect',formatHot($GLOBALS['public']['tuijianSelect']));

        $this->smarty->assign('remenSelect',formatHot($GLOBALS['public']['remenSelect']));

        $this->smarty->assign('classData',$GLOBALS['allclass'][$this->classid]);

        $this->smarty->assign('classid',$this->classid);

        $this->smarty->assign('formdir',ROOT_PATH.'data/form/'.$mid.'.php');

        $this->smarty->display('Content/addcontent.html');

    }

    

    

    //信息初始化、验证并返回

    private function check(){
        $data = p(1,1);

        if(empty($data['title'])) rewrite::js_back('【标题】不能为空');

        if(empty($data['time'])) rewrite::js_back('【发布时间】不能为空');

        //print_r($this->fieldArr);

        //exit();

        foreach($this->fieldArr as $v){

            //必填字段

            if($v['ismust']){

                $mustName[$v['fname']] = $v['ftitle'];

            }

            //多文件或者多图片

            if($v['ftype'] == 'moreimage' || $v['ftype'] == 'morefile' || $v['ftype'] == 'checkbox'){

                $moreField[] = $v['fname'];

            }

            //获取主副表字段

            if($v['vice']){

                $vice[] = $v['fname'];

            }else{

                $lord[] = $v['fname'];

            }

        }

        if($moreField){

            //格式化多元素

            foreach($moreField as $v){

                if($data[$v]){

                    $data[$v] = implode('#####',array_filter($data[$v]));

                }

            }

        }

        if($mustName){

            //验证必填

            foreach($mustName as $k => $v){

                if(!$data[$k]){

                    rewrite::js_back('【'.$v.'】不能为空');

                }

            }

        }

		if(isset($_POST['updateInfo'])){

		   $newData[1]['classid'] = $data['cclassid'];

		}

		if(isset($_POST['addInfo'])){

		   $newData[1]['classid'] = $data['classid'];

		}

        $newData[1]['title'] = $data['title'];

        $newData[1]['tuijian'] = $data['tuijian'];

        $newData[1]['remen'] = $data['remen'];

        $newData[1]['url'] = $data['url'];

        $newData[1]['keywords'] = str_replace('，',',',$data['keywords']);

        $newData[1]['time'] = strtotime($data['time']);



        $newData[1]['description'] = $data['description'];

        $newData[1]['ztid'] = trim(str_replace('，',',',$data['ztid']),',');

        $newData[1]['ztid2'] = trim(str_replace('，',',',$data['ztid2']),',');

        $newData[1]['tagsname'] = trim(str_replace('，',',',$data['tagsname']),',');

        $newData[1]['tagsname2'] = trim(str_replace('，',',',$data['tagsname2']),',');

        //验证专题格式

        if($newData[1]['ztid'] && !preg_match('/^[0-9,]+$/',$newData[1]['ztid'])){

            rewrite::js_back('专题id格式不正确，请正确填写专题id');

        }

        //验证Tags格式

        if($newData[1]['tagsname']){

            $tagsArr = explode(',',$newData[1]['tagsname']);

            foreach($tagsArr as $v){

                if(!preg_match('/^[\x{4e00}-\x{9fa5}]*[A-Za-z0-9]*$/u',$v)){

                    rewrite::js_back('Tags名字只能由【中文、字母、数字】组成');

                }

            }

        }

        if($lord){

            //获取主表数据

            foreach($lord as $v){

                $newData[1][$v] = $data[$v];

            }

        }

        if($vice){

            //获取副表数据

            foreach($vice as $v){

                $newData[2][$v] = $data[$v];

            }

        }

        $newData['tab'] = $GLOBALS['allclass'][$this->classid]['tab'];

        //提取描述

        if(isset($newData[2]['content']) && $newData[2]['content'] && $_POST['is_description']){

             preg_match('/<(.*)>(.*)<\/\1>/U',string::stripslashes(str_replace(array("\r","\n"),'',$newData[2]['content'])),$description);

             if(!$description) $description[2] = $newData[2]['content'];

             $description = string::delHtml($description[2]);

             $description = trim(str_replace('&nbsp;',' ',$description));

             $newData[1]['description'] = string::html_char_dec(lmxstr($description,300));

        }

        //替换关键字链接

        if(isset($_POST['addInfo']) && isset($newData[2]['content']) && $newData[2]['content']){

            $keystr = $GLOBALS['public']['contentkey'];

            $keystr = explode("\n",trim($keystr));

            $keystr = array_filter($keystr);

            foreach($keystr as $v){

                $v = explode('#####',$v);

                $title[] = $v[0];

                $blank = !!$v[2] ? addslashes(' target="_blank"') : '';

                $replace[] = addslashes("<a href='".$v[1]."'$blank>".$v[0]."</a>");

            }

            $title = array_unique($title);

            foreach($title as $k => $v){

                $replaceArr[] = $replace[$k];

            }

            $newData[2]['content'] = str_replace($title,$replaceArr,$newData[2]['content']);

        }

        //提取正文关键字

        if(isset($_POST['is_content_key']) && $newData[2]['content']){

            $split_content = string::html_char_dec($newData[2]['content']);

            $split_content = str_replace(array('&nbsp;',"\r","\n"),array(' ','',''),$split_content);

            $newData[1]['keywords'] = string::split_word($split_content,10);

        }else if(isset($_POST['is_title_key']) && $newData[1]['title']){

            //提取正文关键字

            $split_title = string::html_char_dec($newData[1]['title']);

            $split_title = str_replace(array('&nbsp;',"\r","\n"),array(' ','',''),$split_title);

            $newData[1]['keywords'] = string::split_word($split_title,10);

        }
        //print_r($newData[1]);

		return $newData;

    }

}
?>