<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   前台栏目页面控制器
 */
defined('LMXCMS') or exit();
use Workerman\Lib\Timer;
use GatewayWorker\lib\Gateway;
class RoomAction extends HomeAction{
    public $dataModel;
    public function __construct(){
        parent::__construct();
        if($this->dataModel==null){
            $this->dataModel=new DataModel();
        }

    }
    /*todo 选择游戏
     *参数：roomid uid gemetype
     * 通知对战上方进入所选游戏
    */
    public function choosegame(){
        $gameType=$this->SocketData['gametype'];
        $roomid=$this->SocketData['roomid'];
        $uid=$this->SocketData['uid'];
        //todo 获取对手id
        try{
            $roomData=$this->DataModel->getRoomData($roomid);
            $fitid=$this->DataModel->getFitId($roomid,$uid);
        }catch(Exception $e){
            return;
        }


        //todo 设置游戏双方的游戏类型
        $this->DataModel->setGameType($uid,$roomid,$gameType);
        $this->DataModel->setGameType($fitid,$roomid,$gameType);

        //todo 更新双方的操作时间
        $this->DataModel->updatalastTime($roomid,$uid);
        $this->DataModel->updatalastTime($roomid,$fitid);

        //设置游戏类型  需要判断有权限没有
        $result['m']='goGame';
        $result['gametype']=$gameType;
        if($gameType==4){
            //自己的筛子
            $uidNumber=array(
                rand(1,6),rand(1,6),rand(1,6),rand(1,6),rand(1,6)
            );

            //对方的筛子
            $fitidNumber=array(
                rand(1,6),rand(1,6),rand(1,6),rand(1,6),rand(1,6)
            );
            $this->dataModel->initGameTwo($roomid,$uid,$uidNumber,1);
            $this->dataModel->initGameTwo($roomid,$fitid,$fitidNumber,0);
        }

        //todo 通知对战双方 游戏开始
        Gateway::sendToUid($uid,json_encode($result));
        Gateway::sendToUid($fitid,json_encode($result));
        //当选择大话筛子  生成筛子
    }

    /*todo 请求权限
     *参数：roomid uid
     * 根据uid查询自己的权限  没有不做处理  有
    */
    public function getpower(){
        $roomid=$this->SocketData['roomid'];
        $uid=$this->SocketData['uid'];
        try{
            $this->DataModel->updatalastTime($roomid,$uid);
            $roomData=$this->dataModel->getRoomData($roomid);
        }catch(Exception $e){
            return;
        }

        $reData['m']='choosegame';
        $reData['screen']=$roomData['userData'][$uid]['screen'];
        $reData['power']=$roomData['userData'][$uid]['isMy'];
        Gateway::sendToUid($uid,json_encode($reData));
    }

    //断线重连
    public function reconnect(){

    }
    //查看游戏是否选择
    public function issetgame(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['roomid'];
        $roomData=$this->dataModel->getRoomData($roomid);
        if($roomData==null || !isset($roomData['userData'][$uid]["gametype"])){
            return;
        }
        $gameType=$roomData['userData'][$uid]["gametype"];
        if($gameType==""){
            $result['m']='obegin';
            Gateway::sendToUid($uid,json_encode($result));
        }else{
            $result['m']='goGame';
            $result['gametype']=$gameType;
            Gateway::sendToUid($uid,json_encode($result));
        }
    }
    //确保用户进入的函数
    public function ready(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['roomid'];
        $redRoom="ready_".$roomid;
        $redRoomData=$this->DataModel->getRoomData($redRoom);
        $redRoomData[$uid]=true;
        $this->DataModel->setRoomData($redRoom,$redRoomData);
    }
    public function timeup(){
        $uid=isset($this->SocketData['uid'])?$this->SocketData['uid']:"";
        if($uid==""||$uid<1){
            return;
        }
    }
}
?>