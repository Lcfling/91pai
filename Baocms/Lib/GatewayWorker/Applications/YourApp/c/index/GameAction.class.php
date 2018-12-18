<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 *
 *   前台栏目页面控制器
 */
defined('LMXCMS') or exit();
use GatewayWorker\Lib\Gateway;
class GameAction extends HomeAction{
    public $dataModel;
    public function __construct(){
        parent::__construct();
        if($this->dataModel==null){
            $this->dataModel=new DataModel();
        }
    }

    /*todo 游戏1
     *参数：roomid uid gameData
     * mygameData:当前客户选择 如 石头 剪子  布
     *
     * 没有结果  等待
     *  有结果  gemeData置空 分别通知游戏双方游戏结果
    */
    public function gameOne(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['roomid'];
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
        //自己的数据
        //石头1.剪刀2.布3.
        // 1-2=-1 1赢 前者赢
        //1-3=-2 3赢 后者赢
        //2-3=-1 2赢 前者赢
        //2-1=1 1赢  后者赢
        //3-2=1 2赢  后者赢
        //3-1=2 3赢  前者赢
        if($fitData['gameData']==0){

        }else{
            //游戏逻辑
            $this->DataModel->setGameData();//todo 储存自己的游戏结果

         if($uidnumber==$fitidnumber){
             //双方手势一样  重新开始
             $reData['m']='outGame';
             Gateway::sendToUid($uid,json_encode($reData));
         }elseif($uidnumber==3){//我方为石头的情况下
             //遇到剪刀 我方赢
             if($uidnumber-$fitidnumber=1){
                 $this->DataModel->setGameWin($roomid,$uid);
             }elseif($uidnumber-$fitidnumber=2){//遇到布  我方输
                 $this->DataModel->setGameWin($roomid,$fitid);
             }
             //我方为剪刀的情况下
         }elseif ($uidnumber==2){
             //遇到石头 我方输
             if($uidnumber-$fitidnumber=-1){
                 $this->DataModel->setGameWin($roomid,$fitid);
             }elseif($uidnumber-$fitidnumber=1){//遇到布  我方赢
                 $this->DataModel->setGameWin($roomid,$uid);
             }
             //我方为布的情况下
         }elseif ($uidnumber==1){
             if($uidnumber-$fitidnumber=-2){ //遇到石头 我方赢
                 $this->DataModel->setGameWin($roomid,$uid);
             }elseif($uidnumber-$fitidnumber=-1){//遇到剪刀 我方输
                 $this->DataModel->setGameWin($roomid,$fitid);
             }
         }
            //或许双方胜利数
            $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
            $fitwin=$this->dataModel->getWinData($roomid,$fitid);
            //双方积分是否已够胜利积分
            if($mywin == 2 || $fitwin == 2){
                if($mywin == 2){
                    $this->dataModel->setOutData($roomid,$fitid);//todo 设置用户游戏结束
                    $reData['m']='outGame';
                    Gateway::sendToUid($fitid,json_encode($reData));
                } else{
                    $this->dataModel->setOutData($roomid,$fitid);//todo 设置用户游戏结束
                    $reData['m']='outGame';
                    Gateway::sendToUid($uid,json_encode($reData));
                }

            }else{
                //todo 通知对战双方 游戏继续开始

                //todo 返回参数没有构造
                $reData['m']='outGame';
                Gateway::sendToUid($uid,json_encode($reData));
            }

        }

    }
    //石头剪刀布断线重连
    public function gameConnectOne(){
        ///1.通过选手id找到对应的房间.获取房间数据,
        //2.当out 为true 时直接踢出游戏
        //  2-1当out 为 false时，说明没有人赢，继续游戏
        // 2-2判定有没有晋级 晋级不考虑 不晋级游戏继续
        // 3.判断自己有没有出  不考虑对方出没出
        //  3-1 自己出过 等待结果
        //  3-2 自己没出 出拳
        //
        $uid=$this->SocketData['uid'];//角色id
        $roomid=$this->SocketData['uid'];//房间id
        $gameData=$this->SocketData['gameData'];//自己的参数
        $fitid=$this->DataModel->getFitid($roomid,$uid);//对方id
        $roomData=$this->DataModel->getRoomData($roomid);//房间数据
        $out=$roomData['userData'][$uid]['out'];//胜负状态

        if( $out==true){//输了游戏
            //结束游戏
            $reData['m'] = 'outGame';
            Gateway::sendToUid($uid, json_encode($reData));
    }else{
            $myStatus=$this->SocketData['status'];
            $status = $roomData["status"];
            if ($myStatus == $status || $myStatus == ($status + 1)) {//正确的晋级和没有晋级
                if ($myStatus != $status) {//正常晋级
                    //给前台发送数据，告知进入对应的状态
                    $reData['status'] = $status;
                    //房间号
                    $reData['roomid'] = $roomid;
                    Gateway::sendToUid($uid, json_encode($reData));
                }else{//没有晋级 还在游戏当中
                    //判断自己出没出
                    if($gameData=1 || $gameData=2 || $gameData=3){
                        //自己出过 跳转等待结果页面
                        Gateway::sendToUid($uid,json_encode($roomData));
                    }else{
                        //自己没出 跳转游戏初始化界面
                        Gateway::sendToUid($uid,json_encode($roomData));
                    }

                }
            }

        }
    }
    //大话骰子
    public function gameTwo(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'];

        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间

        $kai=$this->SocketData['kai'];//开骰子
        $uidnumber=$this->SocketData['unumber'];//自己骰子
        $fitidnumber=$this->SocketData['fitid'];//对方骰子

        $this->DataModel->setGameData($roomid,$uid,$this->SocketData);//todo 储存自己的游戏结果
        //游戏逻辑
        $number1=$this->SocketData['number1'];//喊的个数
        $number2=$this->SocketData['number2'];//喊的点数

        //开骰子
        if($kai==1){
            //将双方骰子合并到一个数组
            $number=array_merge($uidnumber,$fitidnumber);
            $count=0;//计算器
            // 1 等于true表示被喊过
            if($one==true){
                //遍历合并的数组 获得点数筛子个数
                foreach ($number as $k=>$v){
                    if($v == $number2){
                        $count++;
                    }
                }
            }else{
                //获得点数筛子个数
                foreach ($number as $k=>$v){
                    if($v == $number2){
                        $count++;
                    }
                }
                $oneCount=0;
                //获得点数1的个数
                foreach ($number as $k=>$v){
                    if($v==1){
                        $oneCount++;
                    }
                }
                $count=$count+$oneCount;
            }

            //  判断结果
            if($count<$number1){
                //上家获得胜利     上家为开塞子的玩家
                $this->DataModel->setGameWin($roomid,$uid);
                $this->dataModel->SetPermission($roomid,$uid);//胜者取消权限
                $this->dataModel->GetPermission($roomid,$fitid);//败者给予权限

            }else{
                //下家获得胜利   下家为被开骰子的玩家
                $this->DataModel->setGameWin($roomid,$fitid);
                $this->dataModel->SetPermission($roomid,$fitid);//胜者取消权限
                $this->dataModel->GetPermission($roomid,$uid);//败者给予权限

            }

            $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
            $fitwin=$this->dataModel->getWinData($roomid,$fitid);
            //双方积分是否已够胜利积分
            if($mywin == 2 || $fitwin == 2){
                if($mywin == 2){
                    $this->dataModel->setOutData($roomid,$fitid);//todo 设置用户游戏结束
                    $reData['m']='outGame';
                    Gateway::sendToUid($fitid,json_encode($reData));
                } else{
                    $this->dataModel->setOutData($roomid,$fitid);//todo 设置用户游戏结束
                    $reData['m']='outGame';
                    Gateway::sendToUid($uid,json_encode($reData));
                }

            }else{
                //todo 通知对战双方 游戏继续开始

                //todo 返回参数没有构造
                $reData['m']='outGame';
                Gateway::sendToUid($uid,json_encode($reData));
            }
        }else{
            //游戏继续...接着喊
            $number1=$this->dataModel->setNumber1($roomid,$fitid);//todo 获取喊的个数
            $number2=$this->dataModel->setNumber2($roomid,$fitid);//todo 获取喊的点数
            $reData['m']='outGame';
            $reData['n1']=$number1;
            $reData['n2']=$number2;
            Gateway::sendToUid($fitid,json_encode($number1,$number2));
        }
    }
    //init
    public function initGame(){
        $uid=$this->SocketData['uid'];//角色id
        $roomid=$this->SocketData['roomid'];//房间id
        $data=$this->dataModel->getGameTwo($roomid,$uid);
        $roomData=$this->dataModel->getRoomData($roomid);
        $reData['m']='getmydata';
        $reData['data']=$data;
        $reData['power']=$roomData['userData'][$uid]['isMy'];
        Gateway::sendToUid($uid,json_encode($reData));
    }

    //大话骰子断线重连
    public function gameConnectTwo(){
        //1.通过选手id找到对应的房间
        //2.获取房间数据,
        //当out 为true 时直接踢出游戏
        //  当out 为 false时，说明没有人赢，继续游戏
        // 判定有没有晋级 晋级不考虑 不晋级游戏继续
        //3.获取对方喊的点数或者自己喊得点数
        //        3-1.对方已喊  显示对方显的点数
        //         3-2.轮到自己.喊对应的点数
        //4.4-1对方有权限 对方计数器-我方计数器=1 我方喊.
        //  4-2对方有权限 对方计数器-我方计数器=0. 对方喊
        //  4-3.我方有权限 我方计数器-对方计数器=1.对方喊
        //  4-4.我方有有权  我方计数器-对方计数器=0.我方喊

        $uid=$this->SocketData['uid'];//角色id
        $roomid=$this->SocketData['uid'];//房间id
        $fitid=$this->DataModel->getFitid($roomid,$uid);//对方id
        $roomData=$this->DataModel->getRoomData($roomid);//房间数据
        $uidnumber=$this->SocketData['unumber'];//自己骰子
        $fitidnumber=$this->SocketData['fitid'];//对方骰子
        $out=$roomData['userData'][$uid]['out'];//胜负状态
        $uidcount=$roomData['userData'][$uid]['count'];//我放计数器
        $fitcount=$roomData['userData'][$fitid]['count'];//对方计数器
        $uidisMy=$roomData['userData'][$uid]['isMy'];//我方权限
        $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
       // $fitwin=$this->dataModel->getWinData($roomid,$fitid);
        $myStatus=$this->SocketData['status'];

        if( $out==true){//输了游戏
            //结束游戏
            $reData['m'] = 'outGame';
            Gateway::sendToUid($uid, json_encode($reData));
        }else {
            //判断有没有晋级
            $status = $roomData["status"];
            if ($myStatus == $status || $myStatus == ($status + 1)) {//正确的晋级和没有晋级
                if ($myStatus != $status) {//正常晋级
                    //给前台发送数据，告知进入对应的状态
                    $reData['status'] = $status;
                    //房间号
                    $reData['roomid'] = $roomid;
                    Gateway::sendToUid($uid, json_encode($reData));
                } else {
                    //游戏继续.判定回合数
                    if ($uidisMy == 1) {//我方权限
                        if ($uidcount - $fitcount == 0) {//我方喊l
                            //游戏继续...接着喊
                            $number1 = $this->dataModel->setNumber1($roomid, $fitid);//todo 获取喊的个数
                            $number2 = $this->dataModel->setNumber2($roomid, $fitid);//todo 获取喊的点数
                            $reData['m'] = 'outGame';
                            Gateway::sendToUid($fitid, json_encode($number1, $number2));
                        } elseif ($uidcount - $fitcount == 1) {//对方喊
                            //获取对方喊得点数
                            $number1 = $this->SocketData['number1'];//喊的个数
                            $number2 = $this->SocketData['number2'];//喊的点数
                        }
                    } else {//对方权限
                        if ($fitcount - $uidcount = 1) {//我方喊
                            //游戏继续...接着喊
                            $number1 = $this->dataModel->setNumber1($roomid, $fitid);//todo 获取喊的个数
                            $number2 = $this->dataModel->setNumber2($roomid, $fitid);//todo 获取喊的点数
                            $reData['m'] = 'outGame';
                            Gateway::sendToUid($fitid, json_encode($number1, $number2));
                        } elseif ($uidcount - $fitcount = 0) {//对方喊
                            //获取对方喊得点数
                            $number1 = $this->SocketData['number1'];//喊的个数
                            $number2 = $this->SocketData['number2'];//喊的点数
                        }
                    }

                }
            }

        }
    }

    //todo 五十五游戏
    /*
     * 用户数据有：左手 右手 喊
     * 喊得数据有判断输赢 ，无数据继续游戏
     * */
    public function gameTri(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'];

        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最后操作时间


        //游戏的逻辑
        /*
         * 双方都有数据情况：
         * 1.获取双方左右手状态，得到数值
         * 2.判断有没有猜中
         *          2.1 猜中
         *              2.1.1 都对 从新开始
         *              2.1.2 一方对 数据处理
         *          2.2 没有 重新开始
         *
         * */


        //获取双方数据
        $myData['lift']=$this->SocketData['lift'];
        $myData['right']=$this->SocketData['right'];
        $myData['shout']=$this->SocketData['shout'];
        $fitData=$roomData["userData"][$fitid]['myGameData'];


        $threeGame = "";
        $threeGame=$myData;
        $this->DataModel->setThreeGameData($roomid,$uid,$threeGame);//存储数据


        if($fitData['gameData']==0){


        }else{
            $this->DataModel->setGameData($roomid,$uid,$this->SocketData);//todo 储存自己的游戏结果
            //统计不出拳的数值
            $count = 0;
            if($myData['lift']==1){
                $count++;
            }
            if($myData['right']==1){
                $count++;
            }
            if($fitData['lift']==1){
                $count++;
            }
            if($fitData['right']==1){
                $count++;
            }

            $myShout =$myData['shout'];
            $fitShout =$fitData['shout'];



            //判断数值
            if($myShout==$count || $count==$fitShout){//有一个猜对了 或者两个都猜对了
                if($myShout==$count && $count==$fitShout){
                    //两个都猜对了 重新开始
                    $reData['m'] = 'againGame';
                    Gateway::sendToUid($uid, json_encode($reData));
                    Gateway::sendToUid($fitid, json_encode($reData));

                }else if($myShout==$count){//自己猜对了

                    $this->DataModel->setGameWin($roomid,$uid);//更新自己赢的数据

                    $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
                    if($mywin == 2) {
                        //通知对手结束游戏
                        $this->dataModel->setOutData($roomid, $fitid);//todo 设置对手游戏结束
                        $reData['m'] = 'outGame';
                        Gateway::sendToUid($fitid, json_encode($reData));
                        //等待下一局开始
                        $reData['m'] = 'waitGame';
                        Gateway::sendToUid($uid, json_encode($reData));
                    }



                }else{//对手猜对了
                    $this->DataModel->setGameWin($roomid,$fitid);//跟新对数赢的数据

                    $fitwin=$this->dataModel->getWinData($roomid,$fitid);
                    if($fitwin == 2) {
                        //通知自己结束游戏
                        $this->dataModel->setOutData($roomid, $uid);//todo 设置自己游戏结束
                        $reData['m'] = 'outGame';
                        Gateway::sendToUid($uid, json_encode($reData));
                        //等待下一局开始
                        $reData['m'] = 'waitGame';
                        Gateway::sendToUid($fitid, json_encode($reData));

                    }

                }

            }else{
                //没有一个猜对 重新开始1
                $this->dataModel->clearGameData();
                $reData['m'] = 'againGame';
                Gateway::sendToUid($uid, json_encode($reData));
                Gateway::sendToUid($fitid, json_encode($reData));
            }

        }
    }

    //五十五 断线重新连接
    /*
     * 1.当out 为true 时直接踢出游戏
     * 2.当out 为 false时，说明没有人赢，继续游戏
     *      2.1判断是否晋级
     *          2.1.1 正常晋级和 不晋级
     *                  若正常晋级 发送正确场次
     *                  判断游戏类型
     *                      为0  前台去选择页面
     *                      为2  前台去大话骰子
     *                      为其他  前台去对应游戏
     *          2.1.2 不正常的晋级 踢出
     *
     * (不管自己出没出过，重新连接后自己都重新出
     * 对手出过   我出过 掉线  一定有结果 重连不考虑
     *             我没出过    重新出
     *  对手没出   我不管出不出重连后   都可以重新出，无影响
     * )
     * ---------jz
     * */
    public function gameConnectTri(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'][$uid];
        $myStatus=$this->SocketData['status'][$uid];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        //$this->dataModel->updatalastTime($roomid,$uid);//todo 更新最后操作时间

        if( $roomData['userData'][$uid]['out']){//输了游戏
            //结束游戏
            $reData['m'] = 'outGame';
            Gateway::sendToUid($uid, json_encode($reData));

        }else{//游戏还没有输！还可以继续！！

            //判断是否晋级下一轮
            //3 == 3      3==3+1
            //3!=3
            $status = $roomData["status"];
            if($myStatus == $status || $myStatus == ($status+1)){//正确的晋级和没有晋级
               if($myStatus != $status){//正常晋级
                   //给前台发送数据，告知进入对应的状态
                   $reData['status'] = $status;
                   //房间号
                   $reData['roomid'] = $roomid;
                   Gateway::sendToUid($uid, json_encode($reData));
               }

                $type=$roomData["userData"][$uid]['gametype'];
                if ($type == '0') {//没有选择游戏
                    //通知前台去选择页面
                    $reData['goGame'] = '0';
                    $reData['roomid'] = $roomid;
                    Gateway::sendToUid($uid, json_encode($reData));
                }else if ($type == '2'){//去大话骰子
                    //通知前台去大话骰子
                    $reData['goGame'] = '2';
                    $reData['roomid'] = $roomid;
                    Gateway::sendToUid($uid, json_encode($reData));
                }else{
                    //去对应的游戏界面
                    $reData['goGame'] = $type;
                    $reData['roomid'] = $roomid;
                    Gateway::sendToUid($uid, json_encode($reData));
                }
            }else{
                //踢出房间
                $reData['m'] = 'outGame';
                Gateway::sendToUid($uid, json_encode($reData));
            }
        }
    }



    //todo 拇指令
    public function gameFour(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $uidData=$roomData["userData"][$uid];
        $fourgame=$roomData["userData"][$uid]['fourgame'];

        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最后操作时间

        $gamedataArray="";
        $gamedataArray['uidhand']=$fourgame['uidhand'];
        $gamedataArray['uidshout']=$fourgame['uidshout'];
        $this->dataModel->setfourgame($roomid,$uid,$gamedataArray);
        if($fitData['gameData']==0 ){//对手放弃出拳

        }
        else{
            //游戏开始
            $uidhand=$fourgame['uidhand'];//自己出的拳数
            $fithand=$fourgame['fithand'];//对方出的拳数
            $this->DataModel->setGameData($roomid,$uid,$this->SocketData);//todo 储存自己的游戏结果
            $number1=$fourgame['uidshout'];//自己喊的拳数
            $number2=$fourgame['fitshout'];//对手喊的拳数
            //游戏逻辑
            $result = ($uidhand + $fithand) % 10;
            if($number1==$number2) {
                //平局，开始新一轮
                $reData='waitgame';
                //todo 返回参数没有构造
                Gateway::sendToUid($fitid,json_encode($reData));
                Gateway::sendToUid($uid,json_encode($reData));
            }
            else{
                if ($result == $number1) {
                    //自己获得胜利
                    $this->DataModel->setGameWin($roomid, $uid);
                    $mywin=$this->dataModel->getWinData($roomid,$uid);
                    if ($mywin == 2) {
                        $this->dataModel->setOutData($roomid, $fitid);//todo 设置用户游戏结束
                        $reData['lose'] = 'outGame';
                        Gateway::sendToUid($fitid, json_encode($reData['lose']));
                        $reData['win'] = 'waitGame';
                        Gateway::sendToUid($uid, json_encode($reData['win']));
                    }
                } else if ($result == $number2) {
                    //对方获胜
                    $this->DataModel->setGameWin($roomid, $fitid);
                    $fitwin = $this->dataModel->getWinData($roomid, $fitid);
                    if ($fitwin == 2) {
                        $this->dataModel->setOutData($roomid, $uid);//todo 设置用户游戏结束
                        $reData['lose'] = 'outGame';
                        Gateway::sendToUid($uid, json_encode($reData['lose']));
                        $reData['win'] = 'waitGame';
                        Gateway::sendToUid($fitid, json_encode($reData['win']));
                    }
                } else {
                    //平局，开始新一轮游戏
                    //todo 通知对战双方 游戏继续开始
                    $reData='waitgame';
                    //todo 返回参数没有构造
                    Gateway::sendToUid($fitid,json_encode($reData));
                    Gateway::sendToUid($uid,json_encode($reData));
                }

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



    //todo 老虎杠子鸡
    public function gameFive(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
        $FourGame=$roomData['userData'][$uid]['FourGame'];
        $uidhands=$FourGame['uidhand'];//自己出的手势
        $fitidhands=$FourGame['fituidhand'];//对方出的手势
        if($fitData['gameData']==0){

        }
        else {
            //游戏逻辑
            if ($uidhands != 1 && $fitidhands != 1) {
                if ($uidhands == $fitidhands + 1) {
                    $this->DataModel->setGameWin($roomid, $uid);

                } else if ($fitidhands == $uidhands + 1) {
                    $this->DataModel->setGameWin($roomid, $fitid);

                } else {
                    //平局，进入下一轮
                }
            }
            if ($uidhands == 1) {
                if ($fitidhands == 4) {
                    $this->DataModel->setGameWin($roomid, $uid);

                } else if ($fitidhands == 2) {
                    $this->DataModel->setGameWin($roomid, $fitid);


                } else {
                    //平局，进入下一局
                }

            }
            if ($fitidhands == 1) {
                if ($uidhands == 4) {
                    $this->DataModel->setGameWin($roomid, $fitid);

                } else if ($uidhands == 2) {
                    $this->DataModel->setGameWin($roomid, $uid);

                } else {
                    //平局，进入下一轮
                }

            }

            $mywin = $this->dataModel->getWinData($roomid, $uid);//自己获取胜利数
            $fitwin = $this->dataModel->getWinData($roomid, $fitid);//对方获取胜利数

            if ($mywin == 2 || $fitwin == 2) {
                if ($mywin == 2) {
                    $this->dataModel->setOutData($roomid, $fitid);//todo 设置用户游戏结束
                    $reData['m'] = 'outGame';
                    Gateway::sendToUid($fitid, json_encode($reData));
                } else {
                    $this->dataModel->setOutData($roomid, $uid);
                    $reData['m'] = 'outGame';
                    Gateway::sendToUid($uid, json_encode($reData));
                }

            } else {
                //todo 通知对战双方 游戏继续开始

                //todo 返回参数没有构造
                $reData['m'] = 'outGame';
                Gateway::sendToUid($uid, json_encode($reData));
            }
        }
    }
    //老虎杠子鸡断线重连
    public function gameConnectFive(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
        $myStatus=$this->SocketData['status'];
        $FourGame=$roomData['userData'][$uid]['FourGame'];
        $uidhands=$FourGame['uidhand'];//自己出的手势
        $fitidhands=$FourGame['fituidhand'];//对方出的手势
        //自己出过拳的情况下 跳转等待结果页面
        //自己没出拳的的情况下 跳转刚进游戏的页面

        if( $roomData['userData'][$uid]['out']){//输了游戏
            //结束游戏
            $reData['m'] = 'outGame';
            Gateway::sendToUid($uid, json_encode($reData));


        }else {//游戏还没有输！还可以继续！！

            //判断是否晋级下一轮
            //3 == 3      3==3+1
            //3!=3
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
                    if($fitidhands=0){
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

    //todo 人在江湖飘
    public function gameSix(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['uid'];
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $fitData=$roomData["userData"][$fitid];
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间

        $FiveGame=$roomData['userData'][$uid]['FiveGame'];
        $uidhands=$FiveGame['uidhand'];//自己出的手势
        $fitidhands=$FiveGame['fituidhand'];//对方出的手势
        if($fitData['gameData']==0){

        }else {
            //游戏逻辑1
            if ($uidhands != 1 && $fitidhands != 1) {
                if ($uidhands == $fitidhands + 1) {
                    $this->DataModel->setGameWin($roomid, $uid);

                } else if ($fitidhands == $uidhands + 1) {
                    $this->DataModel->setGameWin($roomid, $fitid);

                } else {
                    //平局，进入下一轮
                }
            }
            if ($uidhands == 1) {
                if ($fitidhands == 5) {
                    $this->DataModel->setGameWin($roomid, $uid);

                } else if ($fitidhands == 2) {
                    $this->DataModel->setGameWin($roomid, $fitid);


                } else {
                    //平局，进入下一局
                }

            }
            if ($fitidhands == 1) {
                if ($uidhands == 5) {
                    $this->DataModel->setGameWin($roomid, $fitid);

                } else if ($uidhands == 2) {
                    $this->DataModel->setGameWin($roomid, $uid);

                } else {
                    //平局，进入下一轮
                }

            }

            $mywin = $this->dataModel->getWinData($roomid, $uid);//自己获取胜利数
            $fitwin = $this->dataModel->getWinData($roomid, $fitid);//对方获取胜利数

            if ($mywin == 2 || $fitwin == 2) {
                if ($mywin == 2) {
                    $this->dataModel->setOutData($roomid, $fitid);//todo 设置用户游戏结束
                    $reData['m'] = 'outGame';
                    Gateway::sendToUid($fitid, json_encode($reData));
                } else {
                    $this->dataModel->setOutData($roomid, $uid);
                    $reData['m'] = 'outGame';
                    Gateway::sendToUid($uid, json_encode($reData));
                }

            } else {
                //todo 通知对战双方 游戏继续开始

                //todo 返回参数没有构造
                Gateway::sendToUid($uid, json_encode($reData));
            }
        }

    }

    //人在江湖飘断线重连
    public function gameConnectSix(){
        $uid=$this->SocketData['uid'];//角色id
        $roomid=$this->SocketData['uid'];//房间ID
        $mygameData=$this->SocketData['gamedata'];
        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $myStatus=$this->SocketData['status'];
        $fitData=$roomData["userData"][$fitid];
        $FiveGame=$roomData['userData'][$uid]['FiveGame'];
        $uidhands=$FiveGame['uidhand'];//自己出的手势

        if( $roomData['userData'][$uid]['out']){//输了游戏
            //结束游戏
            $reData['m'] = 'outGame';
            Gateway::sendToUid($uid, json_encode($reData));

        }else {//游戏还没有输！还可以继续！！

            //判断是否晋级下一轮
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
}
?>