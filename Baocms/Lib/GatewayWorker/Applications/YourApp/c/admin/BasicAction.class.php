<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   系统配置控制器
 */
defined('LMXCMS') or exit();
class BasicAction extends AdminAction{
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $this->smarty->assign('upload_file_pre',$this->format_arr2str($GLOBALS['public']['upload_file_pre'],'|'));
        $this->smarty->assign('upload_image_pre',$this->format_arr2str($GLOBALS['public']['upload_image_pre'],'|'));
        $this->smarty->assign('q_upload_file_pre',$this->format_arr2str($GLOBALS['public']['q_upload_file_pre'],'|'));
        $this->smarty->display('Basic/basic.html');
    }
    //保存配置
    public function set(){
        $data = p(1,1);
        $weburl=$data['weburl'];
        if(!$weburl){
           rewrite::js_back('网站地址不能为空'); 
        }
        rewrite::regular_back('/\/$/',$weburl,'网站地址必须以“/”结尾');
        $arr['weburl'] = $weburl;
        $global = $data['global'];
        if($global && is_array($global)){
            foreach($global as $v){
                if(empty($v['name']) && empty($v['value'])){
                    continue;
                }
                if(empty($v['name']) && $v['value']){
                    rewrite::js_back('请填写变量名');
                }
                rewrite::regular_back('/^[a-zA-Z0-9_]+$/',$v['name'],'变量名只能由字母、数字、下划线组成');
                //检测重复的变量名
                $checkName[]=$v['name'];
                //赋值
                $v['name'] = $v['name'];
                $arr['global'][$v['name']] = $v['value'];
                $arr['globalType'][$v['name']] = $v['type'];
            }
        }else{
            $arr['global']=array();
            $arr['globalType']=array();
        }
        $checkName=array_count_values($checkName);
        foreach($checkName as $k=>$v){
            if($v > 1){
                rewrite::js_back('【'.$k.'】变量名重复，请检查');
            }
        }
        $arr['webname'] = $data['webname'];
        $arr['keywords'] = str_replace('，',',',$data['keywords']);
        $arr['description'] = $data['description'];
        $arr['ishtml'] = (int)$data['ishtml'];
        $arr['searchtime'] = (int)$data['searchtime'];
        $arr['navsplit'] = $data['navsplit'];
        $arr['isbook'] = (int)$data['isbook'] ? 1 : 0;
        $arr['isbookdata'] = (int)$data['isbookdata'] ? 1 : 0;
        $arr['repeatbook'] = (int)$data['repeatbook'];
        $arr['issmall'] = (int)$data['issmall'];
        $arr['is_ip'] = (int)$data['is_ip'] ? 1 : 0;
        $arr['ip_list'] = $data['ip_list'];
        $arr['sms'] = $data['sms'];
        $arr['is_check_description'] = $data['is_check_description'] ? 1 : 0;
        $arr['is_content_key'] = $data['is_content_key'] ? 1 : 0;
        $arr['is_title_key'] = $data['is_title_key'] ? 1 : 0;
        if(!isset($arr['issmall']) || !$arr['issmall'] || $arr['issmall'] < 0){
            $arr['issmall'] = 0;
        }
        $arr['iswater'] = (int)$data['iswater'];
        if(!isset($arr['iswater']) || !$arr['iswater'] || $arr['iswater'] < 0){
            $arr['iswater'] = 0;
        }
        $arr['small_width'] = (int)$data['small_width'];
        if(!$arr['small_width'] || $arr['small_width'] < 0){
            $arr['small_width'] = 150;
        }
        $arr['small_height'] = (int)$data['small_height'];
        if(!$arr['small_height'] || $arr['small_height'] < 0){
            $arr['small_height'] = 140;
        }
        $markImg = $data['markImg'];
        if($markImg){
            rewrite::regular_back('/^\/(.*)(.jpg|.png|.gif)$/',$markImg,'请正确填写水印图片路径');
        }
        $arr['markImg'] = $markImg ? $markImg : '/data/mark/mark.png';
        //格式化推荐与热门
        $arr['tuijianSelect'] = $data['tuijianSelect'];
        $arr['remenSelect'] = $data['remenSelect'];
        $arr['contentkey'] = $data['contentkey'];
        $arr['bookDisplay'] = (int)$data['bookDisplay'];
        $arr['booknum'] = (int)$data['booknum'];
        $arr['searchnum'] = (int)$data['searchnum'];
        $arr['is_search'] = (int)$data['is_search'] ? 1 : 0;
        $upload_file_pre = $this->format_str2arr($data['upload_file_pre'],'|');
        $upload_image_pre = $this->format_str2arr($data['upload_image_pre'],'|');
        $q_upload_file_pre = $this->format_str2arr($data['q_upload_file_pre'],'|');
        $arr['upload_file_pre'] = $upload_file_pre ? $upload_file_pre : array('zip','rar');
        $arr['upload_image_pre'] = $upload_image_pre ? $upload_image_pre : array('jpg','gif','png');
        $arr['update_max_size'] = (int)$data['update_max_size'] ? (int)$data['update_max_size'] : 20;
        $arr['q_upload_file_pre'] = $q_upload_file_pre ? $q_upload_file_pre : array('zip','rar','jpg','gif','png');
        $arr['q_update_max_size'] = (int)$data['q_update_max_size'] ? (int)$data['q_update_max_size'] : 2;
        //保存缓存文件
        foreach($arr as $k => $v){
            $GLOBALS['public'][$k] = $v;
        }
        f('public/conf',$GLOBALS['public'],true);
        //判断生成静态首页
        if($arr['ishtml'] == 1 && !file::isFile(ROOT_PATH.'index.html')){
            //生成首页index.html
            $this->smarty->template_dir = $this->config['template'].$GLOBALS['public']['default_temdir'].'/'; //模板路径
            $this->smarty->compile_dir=ROOT_PATH.'compile/index/'; //编译文件路径
            $this->smarty->cache_dir = ROOT_PATH.'compile/cache/index/'; //缓存目录
            $schtml = new HtmlModel($this->smarty,$this->config);
            $schtml->sc_home();
            $this->smarty->template_dir = $this->config['curr_template']; //模板路径
            $this->smarty->compile_dir = $this->config['smy_compile_dir'].RUN_TYPE.'/'; //编译文件路径
            $this->smarty->cache_dir = $this->config['smy_cache_dir'].RUN_TYPE.'/'; //缓存目录
        }else{
            //删除index.html文件
            file::unLink(ROOT_PATH.'index.html');
        }
        addlog('修改基本设置');
        rewrite::succ('修改成功',u('Basic','index'));
    }
    //伪静态说明页面
    public function static_sm(){
        $this->smarty->display('Basic/sm.html');
    }
    
    //格式化参数 字符串转数组
    private function format_str2arr($str,$split){
        if($str){
            $arr = explode($split,$str);
            foreach($arr as $k => $v){
                if(!$v) unset($arr[$k]);
            }
            return $arr;
        }
    }
    
    //格式化参数 数组转字符串
    private function format_arr2str($arr,$split){
        if(is_array($arr)){
            foreach($arr as $k => $v){
                if(!$v) unset($arr[$k]);
            }
            return implode($split,$arr);
        }
    }
    
}
?>