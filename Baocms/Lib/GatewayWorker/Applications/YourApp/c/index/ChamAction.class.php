<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   前台栏目页面控制器
 */
defined('LMXCMS') or exit();
use GatewayWorker\Lib\Gateway;
class ChamAction extends HomeAction{
    public $dataModel;
    public function __construct(){
        parent::__construct();
        if($this->dataModel==null){
            $this->dataModel=new DataModel();
        }
    }

    //加入游戏房间内
    public function join(){

    }

    //用户主动离开
    public function leave(){

    }
    //房主踢出房间
    public function getout(){

    }
    //准备
    public function ready(){

    }
    //出场排序
    public function adjustment(){

    }
    //游戏开始
    public function startgame(){

    }
    //返回房间
    public function backroom(){

    }
    //断线重连
    public function reconnect(){

    }

}
?>