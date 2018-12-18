<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 *
 *   前台栏目页面控制器
 */
defined('LMXCMS') or exit();
use GatewayWorker\Lib\Gateway;
class GamefourAction extends HomeAction{
    public $dataModel;
    public function __construct(){
        parent::__construct();
        if($this->dataModel==null){
            $this->dataModel=new DataModel();
        }
    }

    //todo 拇指令
    public function Gamefour(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['roomid'];
        $mygameData=$this->SocketData['gamedata']; //自己的手势
        $this->dataModel->setGameData($roomid, $uid, $mygameData);//存储游戏结果

        $roomData=$this->DataModel->getRoomData($roomid);//房间信息
        $fitid=$this->DataModel->getFitid($roomid,$uid);//对手id
        $fitData=$roomData["userData"][$fitid];//对手的数据

        $fitgameData=$fitData['gameData']; //对手的手势||

        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最后操作时间

        $status=$roomData['status'];
        $sddesc=pow(2,$status-1).'/'.pow(2,$status).'赛段';
        $screen=$roomData['userData'][$uid]['screen'];


        if($fitData['gameData']==0 ){//对手放弃出拳

        }
        else{
            $this->DataModel->setGameData($roomid,$uid,$this->SocketData);//todo 储存自己的游戏结果

            $uidnumber= $mygameData['number']; //我的手势
            $fitidnumber=$fitgameData['number'];//对方手势

            $sum=$uidnumber+$fitidnumber; //双方总和
            $myShout =$mygameData['shout']; // 我喊得
            $fitShout =$fitgameData['shout'];// 对方喊得

            if($myShout !=$sum && $fitShout !=$sum){
                //双手都没猜中 平局
                $win = 0;
                $this->dataModel->clearData($roomid, $uid);
                $this->dataModel->clearData($roomid, $fitid);

            } else if($myShout == $sum && $fitShout == $sum){
                //双方都猜中 平局
                $win = 0;
                $this->dataModel->clearData($roomid, $uid);
                $this->dataModel->clearData($roomid, $fitid);

            }else if($myShout == $sum){
                //自己获胜
                $this->DataModel->setGameWin($roomid, $uid);//更新自己赢的数据
               // $this->DataModel->setGameXwin($roomid,$uid); //小局胜利
               // $this->DataModel->setGameXlost($roomid,$fitid);//失败加一
                $win = 1;
                $this->dataModel->SetPermission($roomid, $uid);//胜者取消权限
                $this->dataModel->GetPermission($roomid, $fitid);//败者给予权限
                $this->dataModel->setGameScreen($roomid, $uid);
                $this->dataModel->setGameScreen($roomid, $fitid);


            } else if ($fitShout == $sum){
                //对方获胜
                $this->DataModel->setGameWin($roomid, $fitid);//跟新对数赢的数据
              //  $this->DataModel->setGameXwin($roomid,$fitid);
            //    $this->DataModel->setGameXlost($roomid,$uid);
                $win = -1;
                $this->dataModel->SetPermission($roomid, $fitid);//胜者取消权限
                $this->dataModel->GetPermission($roomid, $uid);//败者给予权限
                $this->dataModel->setGameScreen($roomid, $uid);
                $this->dataModel->setGameScreen($roomid, $fitid);
            }



            //大局逻辑


            $fitwin=$this->dataModel->getWinData($roomid,$fitid);
            $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
            if($mywin == 2 || $fitwin == 2){
                if($mywin == 2) {
                    //通知对手结束游戏
                    $this->dataModel->setOutData($roomid, $fitid);//todo 设置对手游戏结束
                   // $this->dataModel->setGameBwin($roomid,$uid); //大局胜利场次
          //          $status=$roomData['status'];
                    if($status==1){
                        $this->curl($uid,$roomData['rid'],888);
                        $this->Loutgame($fitid,$roomData['rid'],777);
                        $result['m'] = 'winGame';
                        $result['my'] = $mygameData;
                        $result['you'] = $fitgameData;
                        $result['data']['desc']=$sddesc;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($uid, json_encode($result));
                        //等待下一局开始
                        $result['m'] = 'outGame';
                        $result['my'] = $fitgameData;
                        $result['you']= $mygameData;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($fitid, json_encode($result));
                        $fitwin=$this->dataModel->getWinData($roomid,$fitid);
                    }else{
                        $this->Loutgame($fitid,$roomData['rid'],777);
                        $result['m'] = 'waitGame';
                        $result['my'] = $mygameData;
                        $result['you'] = $fitgameData;
                        $result['data']['desc']=$sddesc;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($uid, json_encode($result));
                        //等待下一局开始
                        $result['m'] = 'outGame';
                        $result['my'] = $fitgameData;
                        $result['you']= $mygameData;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($fitid, json_encode($result));
                        $fitwin=$this->dataModel->getWinData($roomid,$fitid);
                    }

                } else {
                    //通知自己结束游戏
                    $this->dataModel->setOutData($roomid, $uid);//todo 设置自己游戏结束
                  //  $this->dataModel->setGameBwin($roomid,$fitid); // 大局胜利场数
             //       $status=$roomData['status'];
                    if($status==1){
                        $this->curl($fitid,$roomData['rid'],888);
                        $this->Loutgame($uid,$roomData['rid'],777);
                        $result['m'] = 'outGame';
                        $result['my'] = $mygameData;
                        $result['you'] = $fitgameData;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($uid, json_encode($result));
                        //等待下一局开始
                        $result['m'] = 'winGame';
                        $result['my'] = $fitgameData;
                        $result['you']= $mygameData;
                        $result['data']['desc']=$sddesc;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($fitid, json_encode($result));
                    }else{
                        $this->Loutgame($uid,$roomData['rid'],777);
                        $result['m'] = 'outGame';
                        $result['my'] = $mygameData;
                        $result['you'] = $fitgameData;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($uid, json_encode($result));
                        //等待下一局开始
                        $result['m'] = 'waitGame';
                        $result['my'] = $fitgameData;
                        $result['you']= $mygameData;
                        $result['data']['desc']=$sddesc;
                        $result['data']['status']=$roomData['status'];
                        Gateway::sendToUid($fitid, json_encode($result));
                    }

                }
            } else{
                //没有一个猜对 重新开始1
                $result['m'] = 'go';
                $result['data']['desc']=$sddesc;
                $result['data']['screen']=$screen;

                $result['win'] = $win;
                $result['my'] = $mygameData;
                $result['you'] = $fitgameData;
                Gateway::sendToUid($uid, json_encode($result));
                $this->dataModel->clearData($roomid, $uid);


                $result['win'] = 0 - $win;
                $result['my'] = $fitgameData;
                $result['you']= $mygameData;
                Gateway::sendToUid($fitid, json_encode($result));

                $this->dataModel->clearData($roomid, $fitid);
            }

        }
    }

    //拇指战令断线重连
    public function gameConnectFour(){
        $uid=$this->SocketData['uid'];//角色id
        $roomid=$this->SocketData['uid'];//房间ID
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $myStatus=$this->SocketData['status'];
        $fitData=$roomData["userData"][$fitid];
        $fourgame=$roomData["userData"][$uid]['fourgame'];
        $uidhand=$fourgame['uidhand'];//自己出的拳数

        if( $roomData['userData'][$uid]['out']){//输了游戏
            //结束游戏
            $reData['m'] = 'outGame';
            Gateway::sendToUid($uid, json_encode($reData));

        }else {//游戏还没有输！还可以继续！！

            $status = $roomData["status"];
            if ($myStatus == $status || $myStatus == ($status + 1)) {//正确的晋级和没有晋级
                if ($myStatus != $status) {//正常晋级
                    //给前台发送数据，告知进入对应的状态
                    $reData['status'] = $status;
                    //房间号
                    $reData['roomid'] = $roomid;
                    Gateway::sendToUid($uid, json_encode($reData));
                }else{
                    //获取相应的数据   不考虑对方出没出
                    //手势等于0.表示没出拳 跳转刚进游戏的页面
                    if($uidhands=0){
                        //没出拳 通知前端跳转游戏初始页面
                        Gateway::sendToUid($uid,json_encode($roomData));

                    }else{
                        //已出拳.通知前端跳转游戏等待结果页面
                        Gateway::sendToUid($uid,json_encode($roomData));
                    }

                }
            }
        }

    }




    /*todo 客户端 定时轮询
     *参数：roomid uid status
     * status ：当前游戏状态
     *         1：与当前状态不符合 不做处理
     *         2：与当前状态符合
     *                 初始化go=0
     *                 判断所有客户的最后操作时间 超时则判断对战双方胜负计入缓存
     *                 有未出结果  切双方都没有超时  则go=1
     *
     * go=1： 不错处理；
     * go=0： 匹配好游戏 通知客户继续游戏二次匹配
     *
     *
    */
    //晋级
    public function goon(){
        $uid=$this->SocketData['uid'];//角色ID
        $roomid=$this->SocketData['uid'];//房间id
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $out=$this->SocketData['out'];//true为失败 false为胜利
        $reData=$this->SocketData['redata'];//房间信息
        $status=$this->SocketData['status'];//当前游戏状态
        $roomData=$this->DataModel->getRoomData($roomid);//组信息
        $uidTime=$this->dataModel->updatalastTime($roomid,$uid);//自己最后操作更新时间
        $fitTime=$this->dataModel->updatalastTime($roomid,$fitid);//对手最后操作更新时间
        $lastTime=$this->SocketData['lastTime'];//最后游戏操作时间

        $roomdata=$this->SocketData['room'];//当前组
        //1.判断超时
        //1-1.判断双方是否都超时.（双方比id.小的一方胜利）
        //1-2.对方超时 自己胜利
        //1-3.自己超时 对方胜利
        //1-4.没有超时  不做处理

        // 2.判断晋级人数. 人数够的话通知游戏准备
        //2-1.获取当前组的人数
        //2-2.获取胜利的人数
        //2-3.胜利人数是否已够
        //2-4.人数满足 通知客户游戏二次匹配


        
        //双方都超时
        if($uidTime>$lastTime&&$fitTime>$lastTime){
            if($uid>$fitid){
                $this->DataModel->setGameWin($roomid,$uid);//id小于对方.自己胜利
                $this->dataModel->updatalastTime($roomid,$uid);//todo 更新自己最后操作时间
                $this->dataModel->updatalastTime($roomid,$fitid);//todo 更新对手最后操作时间
            }else{
                $this->DataModel->setGameWin($roomid,$fitid);//id大于对方.对方胜利
                $this->dataModel->updatalastTime($roomid,$uid);//todo 更新自己最后操作时间
                $this->dataModel->updatalastTime($roomid,$fitid);//todo 更新对手最后操作时间
            }
            //自己超时 对方胜利
        }elseif ($uidTime>$lastTime){
            $this->DataModel->setGameWin($roomid,$fitid);
            $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最后操作时间
            //对方超时.自己胜利
        }elseif($fitTime>$lastTime){
            $this->DataModel->setGameWin($roomid,$uid);
            $this->dataModel->updatalastTime($roomid,$fitid);//todo 更新最后操作时间
        }

        $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
        $fitwin=$this->dataModel->getWinData($roomid,$fitid);
        //双方积分是否已够胜利积分
        if($mywin == 2 || $fitwin == 2){
            if($mywin == 2){
                $this->dataModel->setOutData($roomid,$fitid);//todo 设置用户游戏结束
                $reData['m']='outGame';
                Gateway::sendToUid($fitid,json_encode($reData));
            }else{
                $this->dataModel->setOutData($roomid,$uid);//todo 设置用户游戏结束
                $reData['m']='outGame';
                Gateway::sendToUid($uid,json_encode($reData));
            }
        }else{
            //todo 通知对战双方 游戏继续开始

            //todo 返回参数没有构造

            Gateway::sendToUid($uid,json_encode($reData));
        }

        $num=$this->SocketData['num']=pow(2,$status);;//当前组人数
        $nums=0;//
        foreach ($roomData as $k=>$v){
            //获取当前组胜利的人数
            $out=$roomdata[$k]['out'];
            if($out == false){
                $nums++;
                //胜利人数已够
                if($nums==$num/2){
                    //通知客户游戏二次匹配
                    $this->DataModel->reRoomData($roomid,$roomdata,$nums);
                    Gateway::sendToUid($uid,json_encode($roomid,$uid));
                }
            }
        }


    }
    public  function timeup(){
        $uid = $this->SocketData['uid'];
        $roomid = $this->SocketData['roomid'];
        $fitid=$this->DataModel->getFitid($roomid,$uid);//获取对手id
        $mywin=$this->DataModel->getWinData($roomid,$uid);//获取胜利数
        $roomData=$this->DataModel->getRoomData($roomid);// 或许房间信息
        $mygameData=isset($roomData['userData'][$uid]['gameData'])?$roomData['userData'][$uid]['gameData']:array();//自己的手势
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
       // $this->dataModel->updatalastTime($roomid,$fitid);//更新对手最后操作时间

        $status=$roomData['status'];
        $sddesc=pow(2,$status-1).'/'.pow(2,$status).'赛段';
        $screen=$roomData['userData'][$uid]['screen'];

        if($this->DataModel->setJoon($roomid,$fitid) == true){

            if($mywin==0){ //小
                $win=1;
                $this->DataModel->setGameWin($roomid,$uid);  //胜利加一
            //    $this->DataModel->setGameXwin($roomid,$uid); //小局胜利
           //     $this->DataModel->setGameXlost($roomid,$fitid);//失败加一
                $this->DataModel->SetPermission($roomid,$fitid);// 取消权限
                $this->DataModel->GetPermission($roomid,$uid);// 获取权限
              //  $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
                $result['win']=$win;
                $result['my']=$mygameData;
                $result['you']=0;
                Gateway::sendToUid($uid,json_encode($result));
                $this->dataModel->clearData($roomid,$uid);


                $result['win']=0-$win;
                $result['my']=0;
                $result['you']=$mygameData;

                Gateway::sendToUid($fitid,json_encode($result));

                $this->dataModel->clearData($roomid,$fitid);
            }else{  //大
                $this->DataModel->setOutData($roomid,$fitid);
             //   $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
               // $this->dataModel->setGameBwin($roomid,$uid); //大局胜利场次
                if($status==1){
                    $this->curl($uid,$roomData['rid'],888);
                    $this->Loutgame($fitid,$roomData['rid'],777);
                    $result['m']='winGame';
                    $result['my']=$mygameData;
                    $result['you']=0;
                    $result['data']['desc']=$sddesc;
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($uid,json_encode($result));
                    $result['m']='outGame';
                    $result['my']=0;
                    $result['you']=$mygameData;
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($fitid,json_encode($result));
                }else{
                    $this->Loutgame($fitid,$roomData['rid'],777);
                    $result['m']='waitGame';
                    $result['my']=$mygameData;
                    $result['you']=0;
                    $result['data']['desc']=$sddesc;
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($uid,json_encode($result));

                    $result['m']='outGame';
                    $result['my']=0;
                    $result['you']=$mygameData;
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($fitid,json_encode($result));
                }
            }
        }
    }
    public function confData(){
        if($this->SocketData['uid'] == ''){return;}else{$uid = $this->SocketData['uid'];}  // 角色ID
        if($this->SocketData['roomid'] == ''){return;}else{$roomid = $this->SocketData['roomid'];}//房间ID
        // if($this->SocketData['gamedata'] == ''){return;}else{$uidnumber = $this->SocketData['gamedata'];}//自己的手势
        if($this->DataModel->getRoomData($roomid) == ''){return ;}else{$roomData=$this->DataModel->getRoomData($roomid);}//获取房间信息
        $uidData=$roomData["userData"][$uid];//获取对手信息
        $mygamedata=$uidData["gameData"];//自己的手势


        if($mygamedata == ''){
            $stuta="failed";
        }else{
            $stuta="success";
        }
        $result['m']='stayGame';
        $result['stuta']=$stuta;
//        die("stuta ==".$stuta);
        Gateway::sendToUid($uid,json_encode($result));

    }
}
?>