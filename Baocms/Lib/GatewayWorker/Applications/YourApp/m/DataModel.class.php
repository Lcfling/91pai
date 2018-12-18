<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 *
 *   内容模块
 */
defined('LMXCMS') or exit();
use GatewayWorker\Lib\Gateway;
class DataModel{
    protected $mmModel=null;
    public function __construct() {
        //global $mem;
        if(!isset($GLOBALS['men'])){
            $GLOBALS['men']=new Redis();    //实例化Memcached类
            $server=array(
                array('127.0.0.1',11211),
            );
            $GLOBALS['men']->addServers($server);
        }
        $this->mmModel=$GLOBALS['men'];
    }
    //获取房间信息
    public function getRoomData($roomid){
        $result=$this->mmModel->get($roomid);
        $result=unserialize($result);
        return $result;
    }
    //获取当前组内人数
    public  function  getCountTeam($roomid ,$team)
    {
        $roomdata =$this->getRoomData($roomid);
        $count =count($roomdata['userdata'][$team]);
        return $count;
    }
    //  进入房间
    public function setUserInTeam($uid,$roomid,$team)
    {
        $roomData =$this->getRoomData($roomid);
        $roomData['userData'][$team][$uid] = array(
            'id'=>$uid,
            'ready'=>0,//是否准备  0 未准备  1准备
            'fitid'=>'',//对手id
            'screen'=>0,//当前游戏对局场次
            'isMy'=>true,//是否是我选择游戏
            'choosegame'=>true,//true 已经选择游戏   30S未更新 切 未选择游戏 做判断
            'win'=>0,// 本局
            'gametype'=>'0',//0 1 2 3 4 5 6/游戏类型
            'gameData'=>'0',//0：没有出  算放弃
            //游戏5数据
            'Fivehands'=>array(
                'uidhands'=>'0',//0表示没出拳/1,2,3,4 代表棒子，虫，鸡，老虎
                'fitidhand'=>'0'//0表示没出拳/1,2,3,4 代表棒子，虫，鸡，老虎
            ),
            //游戏6数据
            'Sixhands'=>array(
                'uidhands'=>'0',//0表示没出拳/1,2,3,4,5 代表一刀，两刀，三刀，四刀，五刀
                'fitidhand'=>'0'//0表示没出拳/1,2,3,4 ,5代表一刀，两刀，三刀，四刀，五刀
            ),
            'lastTime'=>1501151451,//最后操作时间  30S没有更新  则视为放弃  客户端为10s轮询
            'bigover'=>'0',//'1' 有结果
            'joy'=>'master',//order 队长 master 副队长  player 队员
            'out'=>true,//true已经输了  false 继续游戏中 默认false
            'fourgame'=>array(
                'uidhand'=>-1,//0-5表示六种手势,自己
                'uidshout'=>-1,//0-10喊得数字，自己
                'fitdhand'=>-1,//0-5表示六种手势，对手
                'fitshout'=>-1//0-10喊得数字，对手
            ),
            'data'=>array(//游戏统计数据
                'roomid'=>100154,
                'xwin'=>"0",//只要游戏胜利就 +1
                'bwin'=>'0'//在可进入下局对战时 +1
                //todo 记录更多入库数据
            )
        );
        $roomData['alluser'] [$uid]=$uid;
        $this->setRoomData($roomid,$roomData);
    }
//是否所有人准备好了
    function isAllReady($roomid,$team)
    {
        $roomData =$this->getRoomData($roomid);
        $allready =false;
        for($i=0;$i<2;$i++)
        {
            foreach ($roomData['userData'][$team] as $uid => $userdata)
            {
                if($userdata['ready']==0)
                {
                    $allready=false;
                    return $allready;
                }
            }
        }
        $allready =true;
        return $allready;

    }


    public function creatRoom($roomid,$uid){
        $roomData=array(
            'teamA'=>array(
                $uid=>array(
                    'id'=>$uid,
                    'screen'=>0,//当前游戏对局场次
                    'isMy'=>true,//是否是我选择游戏
                    'choosegame'=>true,//true 已经选择游戏   30S未更新 切 未选择游戏 做判断
                    'win'=>0,// 本局
                    'gametype'=>'0',//0 1 2 3 4 5 6/游戏类型
                    'gameData'=>'0',//0：没有出  算放弃

                    'lastTime'=>1501151451,//最后操作时间  30S没有更新  则视为放弃  客户端为10s轮询
                    'bigover'=>'0',//'1' 有结果
                    'out'=>true,//true已经输了  false 继续游戏中 默认false

                    'data'=>array(//游戏统计数据z
                        'roomid'=>100154,
                        'xwin'=>"0",//只要游戏胜利就 +1
                        'bwin'=>'0'//在可进入下局对战时 +1
                        //todo 记录更多入库数据
                    )
                ),
            ),
            'teamB'=>array(
            ),

            'allUser'=>array(
                $uid=>$uid
            ),
        );
        $this->setRoomData($roomid,$roomData);
    }
    public function setRoomData($roomid,$value=array()){
        $value=serialize($value);
        $this->mmModel->set($roomid,$value,86400);
    }
    //保存用户信息1
    public function setfourgame($roomid,$uid,$Data){
        $roomdata=$this->getRoomData($roomid);

        $roomdata['userdata'][$uid]['fourgame']=$Data;
        $roomdata['userdata'][$uid]['gameData']=1;
        $this->setRoomData($roomid,$roomdata);
    }
    //设置游戏类型
    public function setGameType($uid,$roomid,$gametype){
        $roomdata=$this->getRoomData($roomid);
        if(!isset($roomdata['userData'][$uid]['gametype'])){
            error_log("set setGameType error:line 142!");
            $roomdata=$this->getRoomData($roomid);
        }

        $roomdata['userData'][$uid]['gametype']=$gametype;
        $this->setRoomData($roomid,$roomdata);
    }
    //获取对手id
    public function getFitid($roomid,$uid){
        if($roomid>1){
            $roomData=$this->getRoomData($roomid);
        }
        try{
            if(isset($roomData['userData'][$uid]['fitid'])){
                $fitid=$roomData['userData'][$uid]['fitid'];
            }else{
                return false;
            }

        }catch(Exception $e){
            echo "running out-------------------";
            return false;
        }
        return $fitid;
    }

    //对方的手势
    public function getgesture($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
        $fitid=$roomData['userData'][$uid]['fitid'];
        $fitidnumber=$roomData['userData'][$fitid]['number'];
        return $fitidnumber;
    }

    //获取临时房间的信息
    public function getTemRoomData($deal_id){
        $result=$this->mmModel->get($deal_id);
        if($result){
            $result=unserialize($result);
            return $result;
        }else{
            return "";
        }
    }
    //设置临时房间的信息
    public function setTemRoomData($data,$deal_id){
        $value=serialize($data);
        $this->mmModel->set($deal_id,$value,0);
    }
    //加入临时房间
    public function setUidtoTemRoom($uid,$deal_id){
        $value=$this->getTemRoomData($deal_id);
        $value[$uid]=$uid;
        $this->setTemRoomData($value,$deal_id);
    }
    //通过uid剔除临时房间
    public function setUidoutTemRoom($uid,$deal_id){
        $value=$this->getTemRoomData($deal_id);
        if(in_array($uid,$value)){
            unset($value[$uid]);
        }
        $this->setTemRoomData($value,$deal_id);
    }
    //清空临时房间
    public function clearTemRoomData($deal_id){

        $value=array();
        $value=serialize($value);
        $this->mmModel->set($deal_id,$value);
    }
    //获取己方下一名对战成员编号
    public function GetNextuid($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
        //获取下一个对战的序号
        $uidorderNext=$roomData["uidorderPlay"];
        $uidNext=$roomData["teamA"][$uid]["uidorder"]['$uidorderNext'];
        return $uidNext;

    }
    //更新己方uidorderPlay,并判断是否结束比赛
    public function updaData($roomid,$uid,$uidorderPlay){
        $roomData=$this->getRoomData($roomid);
        //更改下个对战的玩家编号
        $uidorderPlay=$uidorderPlay+1;
        $roomData["uidorderPlay"]=$uidorderPlay;

    }
    //获取敌方下一名对战成员编号
    public function GetNextfitid($roomid,$fitid){
        $roomData=$this->getRoomData($roomid);
        //获取下一个对战的序号
        $orderNext=$roomData["fitidorderPlay"];
        $fitidNext=$roomData["teamA"][$fitid]["fitidorder"]['$orderNext'];
        return $fitidNext;

    }
    //更新敌方fitidorderPlay,
    public function updaDatafitid($roomid,$fitid,$fitidorderPlay){
        $roomData=$this->getRoomData($roomid);
        //更改下个对战的玩家编号
        $fitidorderPlay=$fitidorderPlay+1;
        $roomData["fitidorderPlay"]=$fitidorderPlay;


    }
    //获取房间号
    public function getRoomId(){
        $result=$this->mmModel->get('roomid');
        return $result;
    }
    //房间号加1
    public function setRoomAdd()
    {
        $result = $this->mmModel->get('roomid');
        $result += 1;
        $this->mmModel->set('roomid',$result,0);
    }
    //进程开启是调用
    public function intRoomId(){
        $this->mmModel->set('roomid','100001700',0);
    }
    //初始化房间信息
    public function InitRoomData($roomid,$roomData,$nums,$id){

        $status=log($nums,2);
        $temRoomData=$roomData;
        $temRoomData2=array_reverse($roomData,true);
        end($temRoomData);
        //第一次匹配
        $i=1;
        foreach ($roomData as $k=> $value) {
            $fitid=current($temRoomData2);

            $roomDataNew['userData'][$k]=array(
                'id'=>$value,
                'fitid'=>$fitid,//对手id
                'screen'=>1,//当前游戏对局场次
                'isMy'=>false,//是否是我选择游戏
                'choosegame'=>false,//true 已经选择游戏   30S未更新 切 未选择游戏 做判断
                'win'=>0,// 本局
                'gametype'=>'0',//0 1 2 3 4 5/游戏类型
                'gameData'=>'0',//0：没有出  算放弃
                'lastTime'=>time(),//最后操作时间  30S没有更新  则视为放弃  客户端为10s轮询
                'bigover'=>'0',//'1' 有结果
                'out'=>false,//true已经输了  false 继续游戏中 默认false
                'data'=>array(//游戏统计数据
                    'roomid'=>$roomid,
                    'xwin'=>"0",//只要游戏胜利就 +1
                    'bwin'=>'0'//在可进入下局对战时 +1
                    //todo 记录更多入库数据
                )
            );
            if($i%2==0){
                $roomDataNew['userData'][$k]['isMy']=true;
            }
            $i++;
            next($temRoomData2);
        }
        $roomDataNew['status']=$status;
        $roomDataNew['rid']=$id;
        $this->setRoomData($roomid,$roomDataNew);
    }

    public function setSixGamaData($roomid,$uid,$Data){
        $roomData=$this->getRoomData($roomid);
        $roomData['userData'][$uid]['Fivehand']=$Data;
        $roomData['userData'][$uid]['gameData']=1;
        $this->setRoomData($roomid,$roomData);
    }
    public function setFiveGamaData($roomid,$uid,$Data){
        $roomData=$this->getRoomData($roomid);
        $roomData['userData'][$uid]['Sixhand']=$Data;
        $roomData['userData'][$uid]['gameData']=1;
        $this->setRoomData($roomid,$roomData);
    }
    public function reRoomData($roomid){
        $roomData=$this->getRoomData($roomid);
        //二次匹配
        //将获胜的人加入到一个新数组中  用来匹配对手
        $newroomData=array();
        foreach ($roomData['userData'] as $k=>$value){
            $this->clearData($roomid,$k);
            $out=$value['out'];
            if ($out==false){
                $newroomData[]=$value;
            }
        }
        $isMyNum=0;
        //重新分配对手的id
        $newroomData1=array_reverse($newroomData,true);
        foreach ($roomData['userData'] as $k=>$value){
            $out=$value['out'];
            if ($out==false){
                $isMyNum++;
                $temp=current($newroomData1);
                $fitid=$temp['id'];
                $roomData['userData'][$k]=array(
                    'id'=>$roomData['userData'][$k]['id'],
                    'fitid'=>$fitid,//对手id
                    'screen'=>1,//当前游戏对局场次
                    'isMy'=>true,//是否是我选择游戏
                    'choosegame'=>false,//true 已经选择游戏   30S未更新 切 未选择游戏 做判断
                    'win'=>0,// 本局
                    'gametype'=>'0',//0 1 2 3 4 5/游戏类型
                    'gameData'=>'0',//0：没有出  算放弃
                    'lastTime'=>time(),//最后操作时间  30S没有更新  则视为放弃  客户端为10s轮询
                    'bigover'=>'0',//'1' 有结果
                    'out'=>false,//true已经输了  false 继续游戏中 默认false
                    'data'=>$roomData['userData'][$k]['data']
                );
                $roomData['userData'][$k]['fitid']=$fitid;
                if($isMyNum%2==0){
                    $roomData['userData'][$k]['isMy']=true;
                }else{
                    $roomData['userData'][$k]['isMy']=false;
                }
                next($newroomData1);
            }
        }
        $roomData['status']=$roomData['status']-1;
        $this->setRoomData($roomid,$roomData);
    }

    //   一下是临时房间的处理方法
    public function settemRoom(){
    }
    //五十五游戏回写数据
    public function setThreeGameData($roomid,$uid,$GameData){
        $roomData = $this->getRoomData($roomid);
        $roomData['userData'][$uid]['myGameData'] =$GameData;
        $roomData['userData'][$uid]['gameData'] =1;
        $this->setRoomData($roomid,$roomData);
    }


    //游戏大局胜利场次加一
    public  function setGameBwin($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $roomData['userData'][$uid]['bwin']++;
        $this->setRoomData($roomid,$roomData);
    }

   //游戏小局胜利场次加一
    public  function setGameXwin($roomid,$uid){
    $roomData = $this->getRoomData($roomid);
    $roomData['userData'][$uid]['xwin']++;
    $this->setRoomData($roomid,$roomData);
  }
    //游戏胜利场次加一
    public function setGameWin($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        if(isset($roomData['userData'][$uid]['win'])){
            $roomData['userData'][$uid]['win']++;
            $this->setRoomData($roomid,$roomData);
        }

    }
    //游戏失败场次加一
    public function  setGameXlost($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $roomData['userData'][$uid]['xlost']++;
        $this->setRoomData($roomid,$roomData);
    }
    //设置用户游戏结束
    public  function setOutData($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $roomData['userData'][$uid]['out']=true;
        $this->setRoomData($roomid,$roomData);
    }
    //获取游戏胜利数
    public function getWinData($roomid,$uid){
        if($roomid>1){
            $roomData = $this->getRoomData($roomid);
            if(isset($roomData['userData'][$uid]['win'])){
                $win=$roomData['userData'][$uid]['win'];
            }else{
                return false;
            }
        }else{
            return false;
        }
        return $win;
    }
    //设置权限
    public function SetPermission($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $roomData['userData'][$uid]['isMy']=false;
        $this->setRoomData($roomid,$roomData);
    }
    //取消权限
    public function GetPermission($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $roomData['userData'][$uid]['isMy']=true;
        $this->setRoomData($roomid,$roomData);
    }
    //获取筛子个数
    public function setNumber1($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $number1=$roomData['userData'][$uid]['number1'];
        return $number1;
    }
    //获取筛子点数
    public function setNumber2($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $number2=$roomData['userData'][$uid]['number2'];
        return $number2;
    }
    //更新游戏操作时间
    public function updatalastTime($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        if(isset($roomData['userData'][$uid]['lastTime'])){
            $roomData['userData'][$uid]['lastTime']=time();
            $this->setRoomData($roomid,$roomData);
        }
        //return $lastTime;
    }
    //储存自己的游戏结果
    public function  setGameData($roomid,$uid,$value){
        $this->mmModel->set($roomid."-".$uid."-gamedata",$value,86400);
    }
    //获取用户的游戏结果
    public function  getGameData($roomid,$uid){
        $value=$this->mmModel->get($roomid."-".$uid."-gamedata");
        if($value==""){
            $value=0;
        }
        return $value;
    }



    //getUnumber 获取自己的筛子数据
    public function getUnumber($roomid,$uid){
        $roomData = $this->getRoomData($roomid);
        $result=$roomData['userData'][$uid]['unumber'];
        return $result;
    }
    public function initGameTwo($roomid,$uid,$data,$power){
        $roomData=$this->getRoomData($roomid);
        $roomData['userData'][$uid]['initGameData']=$data;
        $roomData['userData'][$uid]['power']=$power;
        $this->setRoomData($roomid,$roomData);
    }
    public function getGameTwo($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
        return $roomData['userData'][$uid]['initGameData'];
    }
    //大话筛子  游戏数据存储
    public function setGameTwoData($roomid,$uid,$n1,$n2){
        $roomData=$this->getRoomData($roomid);
        if(!isset($roomData['userData'][$uid]['count'])){
            $roomData['userData'][$uid]['count']=0;
        }
        $roomData['userData'][$uid]['count']++;
        $roomData['userData'][$uid]['number1']=$n1;
        $roomData['userData'][$uid]['number2']=$n2;
        $this->setRoomData($roomid,$roomData);
    }
    //大话筛主动权更换
    public function setPower($roomid,$uid,$v){
        $roomData=$this->getRoomData($roomid);
        $roomData['userData'][$uid]['power']=$v;
        $this->setRoomData($roomid,$roomData);
    }
    public function clearGameTwoData($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
        $roomData['userData'][$uid]['count']=0;
        $roomData['userData'][$uid]['number1']='';
        $roomData['userData'][$uid]['number2']='';
        $this->setRoomData($roomid,$roomData);
    }
    //大话筛子  点数1是否被叫
    public function isOne($roomid,$uid,$fitid){
        $roomData=$this->getRoomData($roomid);
        $m=$roomData['userData'][$uid]['number2'];
        $f=isset($roomData['userData'][$fitid]['number2'])?$roomData['userData'][$fitid]['number2']:'';
        if($m==1 || $f==1){
            return true;
        }else{
            return false;
        }
    }
    //清除石头剪刀布数据
    public function clearData($roomid,$uid){
        $this->mmModel->set($roomid."-".$uid."-gamedata",0,86400);
    }
    //场次
    public function setGameScreen($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
        $roomData['userData'][$uid]['screen']++;
        $roomData['userData'][$uid]['gametype']="";
        $this->setRoomData($roomid,$roomData);
    }
    // 轮询
    public function setJoon($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
       if(time()-$roomData['userData'][$uid]['lastTime']>70){
           return true;
       }
    }
    //创建 一个正常的房间
    public function creatRoomNomr($uid,$myData,$deal_id,$game_num){
        $roomid=$this->getRoomId();
        $roomList=$this->getRoomList($deal_id);
        $roomList[$roomid]=0;
        $this->setRoomList($deal_id,$roomList);
        $this->setRoomAdd();
        $userList=array($uid=>$myData);
        $roomData['userList']=$userList;
        $roomData['deal_id']=$deal_id;
        $roomData['game_num']=$game_num;
        $roomData['roomid']=$roomid;
        $roomData['type']=0;
        $this->setRoomData($roomid,$roomData);
        return $roomData;
    }
    //创建 一个私有房间 共好友进入 其他人无法匹配进入
    public function creatRoomSelf($uid,$myData,$deal_id,$game_num){
        $roomid=$this->getRoomId();
        $this->setRoomAdd();
        $userList=array($uid=>$myData);
        $roomData['userList']=$userList;
        $roomData['deal_id']=$deal_id;
        $roomData['game_num']=$game_num;
        $roomData['roomid']=$roomid;
        $roomData['type']=9;
        $this->setRoomData($roomid,$roomData);
        return $roomData;
    }

    public function setRoomStatus($roomid){
        $roomData=$this->getRoomData($roomid);
        if(empty($roomData)){
            return;
        }
        $roomData['type']=0;
        $this->setRoomData($roomid,$roomData);
    }


    //加入一个正常的房间
    public function joinRoomNomr($roomid,$uid,$myData){
        $roomData=$this->getRoomData($roomid);
        if(empty($roomData)){
            $result['error']="faild";
            $result['msg']="房间不存在！";
            return $result;
        }
        $deal_id=$roomData['deal_id'];
        $game_num=$roomData['game_num'];
        if(count($roomData['userList'])>=$game_num){
            $result['error']="faild";
            $result['msg']="房间人数已经满员！";
            return $result;
        }
        $roomData['userList'][$uid]=$myData;
        if(count($roomData['userList'])>=$game_num){
            //
            $roomList=$this->getRoomList($deal_id);
            unset($roomList[$roomid]);
            $this->setRoomList($deal_id,$roomList);
            $this->setRoomAdd();
        }
        foreach($roomData as $k=>$v){
            if(isset($roomData['userList'][$k]['readystatus'])){
                $roomData['userList'][$k]['readystatus']=0;
            }
        }
        $this->setRoomData($roomid,$roomData);
        $result['error']="success";
        $result['data']=$roomData;
        return $result;
    }
    //准备
    public function readyNomr($roomid,$uid,$readystatus){
        //$roomData=$this->getRoomData($roomid);

        /*if(empty($roomData)){
            error_log("roomData is empty"." roomid=".$roomid." uid=".$uid);
            $result['error']="faild";
            $result['msg']="房间不存在！";
            return $result;
        }*/
        //error_log("roomData is OKKKKKKK"." roomid=".$roomid." uid=".$uid);
        /*foreach($roomData['userList'] as $k=>$v){
            if(!Gateway::isUidOnline($k)){
                unset($roomData['userList'][$k]);
            }
        }*/
        //修改之单一数据
        $reData['ready']=1;
        $this->setRoomData($roomid."-".$uid,$reData);
        //$roomData['userList'][$uid]['readystatus']=$readystatus;
        //$this->setRoomData($roomid,$roomData);
        $result['error']="success";
        //$result['data']=$roomData;
        return $result;
    }
    public function outRoom($roomid,$uid){
        $roomData=$this->getRoomData($roomid);
        $game_num=$roomData['game_num'];
        $deal_id=$roomData['deal_id'];
        if(empty($roomData)){
            $result['error']="faild";
            $result['msg']="房间不存在！";
            return $result;
        }
        unset($roomData['userList'][$uid]);

        if(count($roomData['userList'])<$game_num){
            //
            $roomList=$this->getRoomList($deal_id);
            $roomList[$roomid]=0;
            $this->setRoomList($deal_id,$roomList);
            //$this->setRoomAdd();
        }
        $this->setRoomData($roomid,$roomData);
        $result['error']="success";
        $result['data']=$roomData;
        return $result;
    }
    public function getReadyStatus($key){
        $res=$this->getRoomData($key);
        if(isset($res['ready'])){
            if($res['ready']==1){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    public function clearReadyStatus($key){
        $reData['ready']=0;
        $this->setRoomData($key,$reData);
    }
    public function Lock($lockid){
        if($this->mmModel->get($lockid)!=true){
            return false;
        }else{
            return true;
        }
    }
    public function setLock($lockid){
        $this->mmModel->set($lockid,true,20);
    }
    public function unLock($lockid){
        $this->mmModel->set($lockid,false,20);
    }
    public function getRoomList($deal_id){
        $value=$this->mmModel->get($deal_id);
        if(!empty($value)){
            $value=unserialize($value);
        }else{
            $value=array();
        }
        return $value;
    }
    public function setRoomList($deal_id,$roomList){
        if(!empty($roomList)){
            $value=serialize($roomList);
            $this->mmModel->set($deal_id,$value);
        }else{
            $roomList=array();
            $value=serialize($roomList);
            $this->mmModel->set($deal_id,$value);
        }
    }
}
?>