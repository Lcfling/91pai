<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   前台控制器基类
 *   次控制器用来判断是否登录
 */
use \GatewayWorker\Lib\Gateway;

defined('LMXCMS') or exit();
class HomeAction extends Action{
    protected $username;
	protected $l; //语言文字
    protected $loginstate;//用户登录状态
    private static $GameData;
    protected function __construct() {
        parent::__construct();
        global $SocketData;

    }
    //更新我的最后操作时间
    public function updatatime(){

    }
    //websocket建立连接
    public function slogin(){
        $uid=$this->SocketData['uid'];
        $client_id=$this->SocketData['client_id'];
        echo "bind success\n";
        $Uarray=Gateway::getClientIdByUid($uid);
        if(!empty($Uarray)){
            foreach($Uarray as $k=>$v){
                Gateway::unbindUid($v,$uid);
            }
        }
        Gateway::bindUid($client_id, $uid);
        $reData['m']='slogin';
        $reData['token']='1';
        Gateway::sendToUid($uid,json_encode($reData));
    }
    public function curl($uid,$deal_id,$type=888){
        $curl=new curl();
        $url="http://xjhapi.taoleyizhan.com/wap/index.php?ctl=game&act=ajaxgameout";
        $post=array('deal_id'=>$deal_id,'uid'=>$uid,'type'=>$type);
        $result=$curl->Post($post,$url);
        return $result;
    }
    public function Loutgame($uid,$deal_id,$type=777){
        $curl=new curl();
        $url="http://xjhapi.taoleyizhan.com/wap/index.php?ctl=game&act=ajaxLgameout";
        $post=array('deal_id'=>$deal_id,'uid'=>$uid,'type'=>$type);
        $result=$curl->Post($post,$url);
        return $result;
    }
    public function ping(){
        $uid=$this->SocketData['uid'];
        $reData['m']='ping';
        $reData['token']='1';
        Gateway::sendToUid($uid,json_encode($reData));
    }
    /*public function Gamefaild($uid,$type){
        $url="";
        if($type=888888){
            $url="http://xjh.taoleyizhan.com/wap/index.php?ctl=game&act=ajaxgameout";
        }
        if($url!=""){
            $curl=new curl();
            $url="http://xjh.taoleyizhan.com/wap/index.php?ctl=game&act=mxlost";
            $post=array('uid'=>$uid,'type'=>$type);
            $result=$curl->Post($post,$url);
            return $result;
        }
    }*/

}
?>