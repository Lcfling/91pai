<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   跳转类
 */
defined('LMXCMS') or exit();
header("Content-type:text/html; charset=utf-8"); 
class rewrite{
    
    //返回上一页的url地址
    public static function backurl(){
        return $_SERVER['HTTP_REFERER'];
    }
    
    //php转向
    public static function php_url($url){
        header("Location:$url");
        exit();
    }
    
    //js转向
    public static function js_url($url){
        echo "<script text='text/javascript'>window.location.href='".$url."'</script>";
        exit;
    }
    
    //成功转向
    public static function succ($str='',$url='',$time=1500,$tem='succ.html'){
        $str = $str ? $str : '操作成功';
        $url = $url ? $url : self::backurl();
        $smartys = lmxSmarty::getSmarty();
        $smartys->assign('time',$time);
        $smartys->assign('str',$str);
        $smartys->assign('url',$url);
        $smartys->display($tem);
        exit();
    }
    
    //js提示错误，并调整上一页
    public static function js_back($str){
        $str = $str ? $str : '发生错误';
        echo "<script type='text/javascript'>alert('$str');history.go(-1);</script>";
        exit();
    }
	//js提示错误，并调整上2页
    public static function js_back2($str){
        $str = $str ? $str : '发生错误';
        echo "<script type='text/javascript'>alert('$str');history.go(-2);</script>";
        exit();
    }
    //错误跳转 
    public static function error($str='',$url='',$time=1500,$tem='error.html'){
        $str = $str ? $str : '发生错误';
        $url = $url ? $url : self::backurl();
        $smartys = lmxSmarty::getSmarty();
        $smartys->assign('time',$time);
        $smartys->assign('str',$str);
        $smartys->assign('url',$url);
        $smartys->display($tem);
        exit();
    }
    
    //验证正则并返回上一页
    public static function regular_back($regular,$var,$str){
        if(!preg_match($regular,$var)){
            self::js_back($str);
            exit();
        }
    }
    
    //执行进度
    static function speed($str){
        echo "<script>$('ul').append('<li>$str</li>');scrollToBottom();</script>";
        ob_flush(); 
        flush();
    }
    //执行进度成功
    static function speedSucc($str){
        echo "<script>$('ul').append('<li><span>$str</span></li>');scrollToBottom();</script>";
    }
    
    //执行进度成功底部文字提示并附加返回按钮
    static function speedInfoBack($str){
        echo "<script type='text/javascript'>$('#temporinfo').html('$str&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'".self::backurl()."\'>[点击返回]</a>');</script>";
    }
}
?>