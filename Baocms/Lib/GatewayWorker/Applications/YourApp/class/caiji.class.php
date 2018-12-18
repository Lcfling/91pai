<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   采集模块
 */
defined('LMXCMS') or exit();
class caiji{
    private $data; //采集规则全部数据
    private $list_url_arr; //列表地址数组
    private $curl;
    private $regularField; //自定义正则字段数据
    private $moduleField; //该模型全部字段数据
    private $forField; //需要循环截取数据的字段
    private $info_page=false;
    private $time_jg;
    private $content_url; //当前采集内容页面的url地址
    public function __construct($data=false){
        if($data){
            $this->data = $data;
            $this->list_url_arr = explode('#####',$this->data['url_str']);
            //取得该模型的所有字段
            $moduleFieldArr = $GLOBALS['allfield'][$this->data['mid']];
            $this->moduleField = tool::arrV2K($moduleFieldArr,'fname');
            //取得自定义字段正则数据
            foreach($this->data['array'] as $k => $v){
                if(isset($this->moduleField[$k])){
                    $this->regularField[$k] = $v;
                }
            }
            $this->time_jg = $data['time_jg'];
        }
        $this->curl = new curl();
    }
    
    //采集内容页面
    public function StartCj($content_url){
        //检测是否有内容分页
        if($this->data['is_info_page']){
            $param['info_page_regular'] = $this->data['info_page_regular'];
            $urlpre = getDomain($content_url);
            //递归获取内容页码
            $this->info_page = $this->forContentPage(array('content'=>array($content_url)),$urlpre,$param);
            //赋值需要重复截取的字段名字
            foreach($this->data['array'] as $k=>$v){
                if(isset($v['is_for'])){
                    $this->forField[] = $k;
                }
            }
            $this->content_url = $this->info_page[0];
            //遍历分页，采集分页数据
            $is_for = true;
            foreach($this->info_page as $v){
                if($k != 0) $is_for = false;
                $data[] = $this->caiji_content($v,$is_for);
                if($this->time_jg) sleep($this->time_jg); //设置等待时间
            }
            //整合分页数据
            $data = $this->cjDataMerge($data);
        }else{
            $this->content_url = $content_url;
            $data = $this->caiji_content($content_url);
        }
        $tags2zt = $this->tags2zt($data); //tags和专题数据
        return array_merge($data,$tags2zt);
    }
    
    //处理tags和专题
    private function tags2zt($newData){
        $data['tagsname'] = $this->data['tagsname'] ? str_replace('，',',',$this->data['tagsname']) : '';
        $data['ztid'] = $this->data['ztid'] ? str_replace('，',',',$this->data['ztid']) : '';
        $data['ztid'] = explode(',',$data['ztid']);
        $data['ztid'] = array_unique($data['ztid']);
        $data['ztid'] = implode(',',$data['ztid']);
        //处理tags
        if($this->data['is_fenci_tags'] && $newData['title']){
            $tagsname = string::split_word($newData['title'],15);
            $tagsname = explode(',',$tagsname);
            if($data['tagsname']){
                $tagsname2 = explode(',',$data['tagsname']);
            }else{
                $tagsname2 = array();     
            }
            $tagsname = array_merge($tagsname,$tagsname2);
            $tagsname = array_unique($tagsname);
            $data['tagsname'] = implode(',',$tagsname);
        }
        return $data ? $data : array();
    }
    
    //返回测试采集数据
    public function testCaiji(){
        $content_url = $this->caiji_list_content($this->list_url_arr[0]);//进入列表页面获取内容地址
        if($content_url['succ']){
            //检测是否有内容分页
            $this->content_url = $content_url['content'][0];
            if($this->data['is_info_page']){
                $param['info_page_regular'] = $this->data['info_page_regular'];
                $urlpre = getDomain($content_url['content'][0]);
                //递归获取内容页码
                $this->info_page = $this->forContentPage(array('content'=>array($content_url['content'][0])),$urlpre,$param);
                //赋值需要重复截取的字段名字
                foreach($this->data['array'] as $k=>$v){
                    if(isset($v['is_for'])){
                        $this->forField[] = $k;
                    }
                }
                //遍历分页，采集分页数据
                $is_for = true;
                foreach($this->info_page as $k=>$v){
                    if($k != 0) $is_for = false;
                    $data[] = $this->caiji_content($v,$is_for);
                    if($this->time_jg) sleep($this->time_jg); //设置等待时间
                }
                //整合分页数据
                $data = $this->cjDataMerge($data);
            }else{
                $data = $this->caiji_content($content_url['content'][0]);
            }
            $tags2zt = $this->tags2zt($data); //tags和专题数据
            return array_merge($data,$tags2zt);
        }else{
            exit($content_url['content']);
        }
    }
    
    //根据内容页面地址返回采集到的数据
    public function caiji_content($url,$is_for=true){
        //获取内容页面内容
        $html = $this->curl->getContent($url);
        if(!$this->curl->succ) return false;
        //转换编码
        $html = $this->encode_replaces($html);
        $field_sys_arr = array();
        $field_dy_arr = array();
        //截取系统字段
        if($is_for) $field_sys_arr = $this->caiji_sys_field($html);
        //截取自定义字段
        $field_dy_arr = $this->caiji_zdy_field($html,$is_for);
        //替换url
        foreach($field_dy_arr as $k => $v){
            if(($this->moduleField[$k]['ftype'] == 'image' || $this->moduleField[$k]['ftype'] == 'file') && trim($v)){
                $v = $this->buquanUrl(trim($v));
            }
            if(($this->moduleField[$k]['ftype'] == 'moreimage' || $this->moduleField[$k]['ftype'] == 'morefile') && trim($v)){
                $urlStr = array();
                $v = explode('#####',$v);
                foreach($v as $i){
                    $urlStr[] = $this->buquanUrl($i);
                }
                $v = implode('#####',$urlStr);
            }
            $field_dy_arr[$k] = $v;
        }
        $fieldData = array_merge($field_sys_arr,$field_dy_arr);
        foreach($fieldData as $k => $v){
            $fieldData[$k] = $this->removeHtml($v);
        }
        return $fieldData;
    }
    //编码转换
    private function encode_replaces($html){
        $encode = mb_detect_encoding($html,array('GB2312','GBK','UTF-8','BIG-5',));
        if($encode != 'UTF-8'){
            $html = iconv($encode,'UTF-8',$html);
        }
        return $html;
    }
    //把采集到的分页数据整合到一起
    public function cjDataMerge($data){
        foreach($this->regularField as $k=>$v){
            if(!$v['is_for']) continue;
            $this->forField[] = $k;
        }
        foreach($data as $j=>$v){
            if($j == 0) continue;
            foreach($v as $k=>$i){
                if(in_array($k,$this->forField)){
                    //开始组合数据
                    if($this->moduleField[$k]['ftype'] == 'editor'){ //编辑器
                        $data[0][$k] = $data[0][$k].$i;
                    }else if($this->moduleField[$k]['ftype'] == 'moreimage' || $this->moduleField[$k]['ftype'] == 'morefile'){
                        if($i){
                            $data[0][$k] = $data[0][$k].'#####'.$i;
                        }
                    }
                }
            }
        }
        foreach($data[0] as $k=>$v){
            $arr = array();
            if($this->moduleField[$k]['ftype'] == 'moreimage' || $this->moduleField[$k]['ftype'] == 'morefile'){
                $arr = explode('#####',$v);
                $arr = array_filter($arr,'removeArraySpace');
                $v = implode('#####',$arr);
            }
            $newData[$k] = $v;
        }
        return $newData;
    }
    //补全拼接图片地址
    private function buquanUrl($imgurl){
        $imgurl = str_replace('./','/',$imgurl);
		if(!preg_match('/^http:\/\//',$imgurl)){
            if(!preg_match('/^\//',$imgurl)){
                $imgurl = dirname($this->content_url).'/'.$imgurl;
            }else{
                $imgurl = getDomain($this->content_url).$imgurl;
            }
        }
        return $imgurl;
    }
    
    //截取自定义字段
    public function caiji_zdy_field($html,$is_for){
        $data = array();
        foreach($this->regularField as $k => $v){
                if(!$is_for && !in_array($k,$this->forField)) continue; //过掉不需要重复截取的字段
                if($v['default']){
                    $data[$k] = $v['default'];
                }else{
                    $regulat = pregStr($this->data['array'][$k]['regular'],'[-lmx_'.$k.'-]'); //转义正则
                    preg_match('/'.$regulat.'/Ui',$html,$str);
                    $data[$k] = $str[1];
                    if($v['boximg']){
                        //截取区域中所有图片
                        preg_match_all('/<img[^>]*src=[\'\"]*?([^\'\"]*?)[\'\"]*?[^>]*>/Ui',$str[1],$imgMore);
                        $data[$k] = implode('#####',$imgMore[1]);
                    }else if($v['boxoneimage']){
                        //截取区域中第一张图片
                        $regulat = pregStr($this->data['array'][$k]['regular'],'[-lmx_'.$k.'-]'); //转义正则
                        preg_match('/'.$regulat.'/Ui',$html,$str);
                        preg_match('/<img[^>]*src=[\'\"]*?([^\'\"]*?)[\'\"]*?[^>]*>/Ui',$str[1],$imgMore);
                        $data[$k] = $imgMore[1] ? $imgMore[1] : '';
                    }else{
                        if($this->data['str_y_replace']){
                            $data[$k] = $this->content_str_replace($data[$k]);
                        }
                        if($this->data['str_remove']){
                            $data[$k] = $this->content_preg_remove($data[$k]);
                        }
                        if($this->data['remove_html']){
                            $data[$k] = $this->removeHtml($data[$k]);
                        }
                    }
                }
        }
        return $data;
    }
    
    //截取系统字段
    public function caiji_sys_field($html){
        //截取标题
        $data['title'] = $this->data['array']['title']['default'];
        if(!$this->data['array']['title']['default']){
            $regulat = pregStr($this->data['array']['title']['regular'],'[-lmx_title-]'); //转义正则
			if($this->data['array']['title']['regular'])
			{
			preg_match('/'.$regulat.'/Ui',$html,$str);
			}else{
            preg_match('/<title>(.*)<\/title>/Ui',$html,$str);
			}
            $data['title'] = $str[1];
        }
        if($this->data['str_y_replace']){
            $data['title'] = $this->content_str_replace($data['title']);
        }
        if($this->data['str_remove']){
            $data['title'] = $this->content_preg_remove($data['title']);
        }
        //截取关键字
        $data['keywords'] = $this->data['array']['keywords']['default'];
        if($this->data['array']['keywords']['is_fenci']){
            //分词
            $data['keywords'] = string::split_word($data['title'],10);
        }else if(!$this->data['array']['keywords']['default']){
            $regulat = pregStr($this->data['array']['keywords']['regular'],'[-lmx_keywords-]'); //转义正则
            preg_match('/'.$regulat.'/Ui',$html,$str);
            $data['keywords'] = $str[1];
        }
        if($this->data['str_y_replace']){
            $data['keywords'] = $this->content_str_replace($data['keywords']);
        }
        if($this->data['str_remove']){
            $data['keywords'] = $this->content_preg_remove($data['keywords']);
        }
        //截取描述
        $data['description'] = $this->data['array']['description']['default'];
        if(!$this->data['array']['description']['default']){
            $regulat = pregStr($this->data['array']['description']['regular'],'[-lmx_description-]'); //转义正则
            preg_match('/'.$regulat.'/Ui',$html,$str);
            $data['description'] = $str[1];
        }
        if($this->data['str_y_replace']){
            $data['description'] = $this->content_str_replace($data['description']);
        }
        if($this->data['str_remove']){
            $data['description'] = $this->content_preg_remove($data['description']);
        }
        return $data;
    }
    
    //清理标签
    private function removeHtml($str){
        //清理iframe
        if(isset($this->data['remove_html']['iframe'])){
            $str = preg_replace('/<iframe[^>]*>[\s\S]*<\/iframe>/Ui','',$str);
        }
        if(isset($this->data['remove_html']['script'])){
            $str = preg_replace('/<script[^>]*>[\s\S]*<\/script>/Ui','',$str);
        }
        if(isset($this->data['remove_html']['style'])){
            $str = preg_replace('/<style[^>]*>[\s\S]*<\/style>/Ui','',$str);
        }
        if(isset($this->data['remove_html']['table'])){
            $str = preg_replace('/<table[^>]*>[\s\S]*<\/table>/Ui','',$str);
        }
        if(isset($this->data['remove_html']['a'])){
            $str = preg_replace('/<a[^>]*>([\s\S]*)<\/a>/Ui','$1',$str);
        }
        if(isset($this->data['remove_html']['span'])){
            $str = preg_replace('/<span[^>]*>([\s\S]*)<\/span>/Ui','$1',$str);
        }
        if(isset($this->data['remove_html']['strong'])){
            $str = preg_replace('/<strong[^>]*>([\s\S]*)<\/strong>/Ui','$1',$str);
        }
        if(isset($this->data['remove_html']['b'])){
            $str = preg_replace('/<b[^>]*>([\s\S]*)<\/b>/Ui','$1',$str);
        }
        if(isset($this->data['remove_html']['font'])){
            $str = preg_replace('/<font[^>]*>([\s\S]*)<\/font>/Ui','$1',$str);
        }
        if(isset($this->data['remove_html']['img'])){
            $str = preg_replace('/<img[^>]*>/Ui','',$str);
        }
        return $str;
    }
    
    //字符替换
    private function content_str_replace($str){
        $str_y_replace = explode(',',$this->data['str_y_replace']);
        $str_n_replace = explode(',',$this->data['str_n_replace']);
        return str_replace($str_y_replace,$str_n_replace,$str);
    }
    
    //正则删除
    private function content_preg_remove($str){
        foreach(explode("\n",$this->data['str_remove']) as $v){
            $regular = pregStr(trim($v));
            $str = preg_replace('/'.$regular.'/Ui','',$str);
        }
        return $str;
    }
    
    //采集列表页面 返回该列表所有内容页面地址 返回succ和content俩个值的数组，succ判断真或者假，content输出内容或者错误信息
    public function caiji_list_content($url){
        $host = getDomain($url);
        $param['content_url_box'] = $this->data['content_url_box'];
        $param['content_url_regular'] = $this->data['content_url_regular'];
        $content_url = $this->getContentUrl($url,$host,$param);
        return $content_url;
    }
    
    //根据一条列表地址采集数据
    public function list_caiji($list_url){
        $list_content = $this->curl->getContent($list_url);
        if(!$this->curl->succ) return false;
        return $list_content;
    }
    
    //返回内容页面分页
    public function getContentPage($contentUrl,$host,$param){
        $newUrl = array();
        //$param['content_url'] 当前内容页面地址
        //$param['info_page_regular'] 页码区域正则
        $pathurl = parse_url($contentUrl);
        if(!preg_match('/\.[\w]+$/',$pathurl['path'])){
            $contentUrl = trim($contentUrl,'/').'/';
        }
        $boxRegular = string::stripslashes($param['info_page_regular']);
        $content = $this->curl->getContent($contentUrl);
        $data = array(
            'succ' => false,
            'content' => '',
        );
        if($this->curl->succ){
            $data['succ'] = true;
            //截取链接区域
            $boxRegular = pregStr($boxRegular,'[-info_page_box-]'); //转义正则
            preg_match('/'.$boxRegular.'/Ui',$content,$boxStr);
            //截取地址
            preg_match_all('/<a[^>]*href=[\'\"]*?([^\s\'\">]*?)[\'\"]*?[^>]*>/Ui',$boxStr[1],$urlArr);
            array_unshift($urlArr[1],$contentUrl);
            foreach($urlArr[1] as $v){
                $urlInfo = parse_url($v);
                if($urlInfo['path'] && preg_match('/[\w]+$/',$urlInfo['path'])){
                    if(!preg_match('/^\//',$v) && !preg_match('/http:\/\//',$v)){
                        $newUrl[] = dirname($contentUrl).'/'.$v;
                    }else{
                        $newUrl[] = addHost($v,$host);
                    }
                }
            }
            $data['content'] = array_unique($newUrl);
        }else{
            $data['succ'] =false;
            $data['content'] = $this->curl->error;
        }
        return $data;
    }
    
    //递归循环获取内容页面所有页码
    public function forContentPage($data,$urlpre,$param,$arr=array()){
        $arr = array_merge($arr,$data['content']);
        $url = $this->getContentPage($data['content'][count($data['content'])-1],$urlpre,$param);
        if($url['content']){
            foreach($url['content'] as $v){
                if(!in_array($v,$arr)){
                    $a[] = $v;
                }
            }
            if($a){
                if($this->time_jg) sleep($this->time_jg); //设置等待时间
                $arr = array_merge($arr,$this->forContentPage(array('content' => $a),$urlpre,$param,$arr));
            }
        }
        return array_unique($arr);
    }
    //提取目标列表页面内容地址
    public function getContentUrl($listUrl,$host,$param){
        $newUrl = array();
        //$param['content_url_box'] 内容区域正则
        //$param['content_url_regular'] 内容地址正则
        $pathurl = parse_url($listUrl);
        if(!preg_match('/\.[\w]+$/',$pathurl['path'])){
            $listUrl = trim($listUrl,'/').'/';
        }
        $boxRegular = string::stripslashes($param['content_url_box']);
        $urlRegular = string::stripslashes($param['content_url_regular']);
        $content = $this->curl->getContent($listUrl);
        $data = array(
            'succ' => false,
            'content' => '',
        );
        if($this->curl->succ){
            $data['succ'] = true;
            //截取链接区域
            $boxRegular = pregStr($boxRegular,'[-content_url_box-]'); //转义正则
            preg_match('/'.$boxRegular.'/Ui',$content,$boxStr);
            //截取地址
            $urlRegular = pregStr($urlRegular,'[-url-]'); //转义正则
            preg_match_all('/'.$urlRegular.'/Ui',$boxStr[1],$urlArr);
            foreach($urlArr[1] as $v){
                if(!preg_match('/^\//',$v) && !preg_match('/http:\/\//',$v)){
                    $newUrl[] = dirname($listUrl).'/'.$v;
                }else{
                    $newUrl[] = addHost($v,$host);
                }
            }
            $data['content'] = $newUrl;
        }else{
            $data['succ'] =false;
            $data['content'] = $this->curl->error;
        }
        return $data;
    }
}
?>