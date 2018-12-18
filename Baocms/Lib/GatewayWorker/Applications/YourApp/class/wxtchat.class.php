<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   采集类
 */
defined('LMXCMS') or exit();
class wxchat{

    public function __construct(){
    }
    
    //GET方式返回目标页面内容
    public function getContent($url){
        $content=file_get_contents($url);
		$this->succ=true;
		return $content;
		curl_setopt($this->ch,CURLOPT_URL,$url);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,0);
        //伪造百度蜘蛛IP  
        curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('X-FORWARDED-FOR:'.$this->ip.'','CLIENT-IP:'.$this->ip.'')); 
        //伪造百度蜘蛛头部
        curl_setopt($this->ch,CURLOPT_USERAGENT,"Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)");
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_HEADER,0);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $content = curl_exec($this->ch);
        if($content === false){//输出错误信息
            $no = curl_errno($this->ch);
            switch(trim($no)){
                case 28 : $this->error = '访问目标地址超时'; break;
                default : $this->error = curl_error($this->ch); break;
            }
            return $this->error;
        }else{
            $this->succ = true;
            return $content;
        }
    }
	//POST方式登录微信控制台
    public function loginWeixin($url,$cookie,$post){
        $curl = curl_init();//初始化curl模块 
        curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址 
        curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息 
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中 
        curl_setopt($curl, CURLOPT_POST, 1);//post方式提交 
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息 
        curl_exec($curl);//执行cURL 
        curl_close($curl);//关闭cURL资源，并且释放系统资源 
    }
    //下载图片到本地  传入原图片网址，保存地址，不包包含图片后缀
    public function downImg($imgUrl,$path){
        $imgUrl = preg_replace_callback('/[\x{4e00}-\x{9fa5}A-Za-z0-9_]/u',"preg_callback_chinaese",$imgUrl);
        curl_setopt($this->ch,CURLOPT_URL,$imgUrl);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,0);
        curl_setopt($this->ch,CURLOPT_HEADER,1);
        //伪造百度蜘蛛IP
        curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('X-FORWARDED-FOR:'.$this->ip.'','CLIENT-IP:'.$this->ip.''));
        //伪造百度蜘蛛头部
        curl_setopt($this->ch,CURLOPT_USERAGENT,"Mozilla/5.0 (compatible; Baiduspider-image/2.0; +http://www.baidu.com/search/spider.html)");
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_NOBODY,1);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $zt = curl_exec($this->ch);
        if(strpos($zt,'200') === false) return false;
        curl_setopt($this->ch,CURLOPT_NOBODY,0);
        curl_setopt($this->ch,CURLOPT_HEADER,0);
        $img = curl_exec($this->ch);
        $imgInfo = pathinfo($imgUrl);
        file_put_contents($path.'.'.$imgInfo['extension'],$img);
        return str_replace(ROOT_PATH,'',$path.'.'.$imgInfo['extension']);
    }
	//短信post提交
	public function Postsms($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
    }
    
    public function __destruct() {
        curl_close($this->ch);
    }
}
?>