<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 *
 *   前台栏目页面控制器
 */
defined('LMXCMS') or exit();
use GatewayWorker\Lib\Gateway;
class GametwoAction extends HomeAction{
    public $dataModel;
    public function __construct(){
        parent::__construct();
        if($this->dataModel==null){
            $this->dataModel=new DataModel();
        }
    }


    //大话骰子
    public function gameTwo(){
        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['roomid'];
        $mygameData=isset($this->SocketData['gamedata'])?$this->SocketData['gamedata']:'0';

        $roomData=$this->DataModel->getRoomData($roomid);
        $fitid=$this->DataModel->getFitid($roomid,$uid);
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间

        $kai=isset($this->SocketData['kai']) ? '1' : '0';//开骰子
        $sd=$roomData['status'];
        $sddesc=pow(2,$sd-1).'/'.pow(2,$sd).'赛段';
        $screen=$roomData['userData'][$uid]['screen'];
        //todo 储存自己的游戏结果
        //游戏逻辑
        $number1=isset($this->SocketData['number1'])?$this->SocketData['number1']:'';//喊的个数
        $number2=isset($this->SocketData['number2'])?$this->SocketData['number2']:'';//喊的点数

        $this->DataModel->setGameTwoData($roomid,$uid,$number1,$number2);
        $one=$this->dataModel->isOne($roomid,$uid,$fitid);
        //开骰子
        if($kai==1){
            //将双方骰子合并到一个数组
            $uidnumber=$roomData['userData'][$uid]['initGameData'];
            $fitidnumber=$roomData['userData'][$fitid]['initGameData'];
            $number=array_merge($uidnumber,$fitidnumber);
            $count=0;//计算器
            $fitnumber2=$roomData['userData'][$fitid]['number2'];
            // 1 等于true表示被喊过
            if($one==true){
                //遍历合并的数组 获得点数筛子个数
                foreach ($number as $k=>$v){
                    if($v == $fitnumber2){
                        $count++;
                    }
                }
            }else{
                //获得点数筛子个数
                foreach ($number as $k=>$v){
                    if($v == $fitnumber2){
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
            $fitnumber1=$roomData['userData'][$fitid]['number1'];
            //  判断结果
            if($count<$fitnumber1){
                //上家获得胜利     上家为开塞子的玩家
                $this->DataModel->setGameWin($roomid,$uid);
                $this->dataModel->SetPermission($roomid,$uid);//胜者取消权限
                $this->dataModel->GetPermission($roomid,$fitid);//败者给予权限
                $win='1';//自己胜利

            }else{
                //下家获得胜利   下家为被开骰子的玩家
                $this->DataModel->setGameWin($roomid,$fitid);
                $this->dataModel->SetPermission($roomid,$fitid);//胜者取消权限
                $this->dataModel->GetPermission($roomid,$uid);//败者给予权限
                $win='0';//对手胜利
            }
            $this->dataModel->setGameScreen($roomid, $uid);
            $this->dataModel->setGameScreen($roomid, $fitid);
            $mywin=$this->dataModel->getWinData($roomid,$uid);//todo 获取胜利数
            $fitwin=$this->dataModel->getWinData($roomid,$fitid);
            $this->dataModel->clearGameTwoData($roomid,$uid);
            $this->dataModel->clearGameTwoData($roomid,$fitid);
            //双方积分是否已够胜利积分
            if($mywin == 2 || $fitwin == 2){
                if($mywin == 2){
                    $this->dataModel->setOutData($roomid,$fitid);//todo 设置用户游戏结束
                    if($sd=='1'){
                        $this->curl($uid,$roomData['rid'],888);
                        $this->Loutgame($fitid,$roomData['rid'],777);
                        $reData['m']='winGame';
                        $reData['data']['status']=$roomData['status'];
                        $reData['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                        $reData['data']['desc']=$sddesc;
                        Gateway::sendToUid($uid,json_encode($reData));
                        $reData['m']='outGame';
                        $reData['data']['fitData']=$roomData['userData'][$uid]['initGameData'];
                        Gateway::sendToUid($fitid,json_encode($reData));
                    }else{
                        $this->Loutgame($fitid,$roomData['rid'],777);
                        $reData['m']='waitGame';
                        $reData['data']['status']=$roomData['status'];
                        $reData['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                        $reData['data']['desc']=$sddesc;
                        Gateway::sendToUid($uid,json_encode($reData));
                        $reData['m']='outGame';
                        $reData['data']['fitData']=$roomData['userData'][$uid]['initGameData'];
                        Gateway::sendToUid($fitid,json_encode($reData));
                    }

                } else{
                    $this->dataModel->setOutData($roomid,$uid);//todo 设置用户游戏结束
                    if($sd=='1'){
                        $this->curl($fitid,$roomData['rid'],888);
                        $this->Loutgame($uid,$roomData['rid'],777);
                        $reData['m']='winGame';
                        $reData['data']['status']=$roomData['status'];
                        $reData['data']['fitData']=$roomData['userData'][$uid]['initGameData'];
                        $reData['data']['desc']=$sddesc;
                        Gateway::sendToUid($fitid,json_encode($reData));
                        $reData['m']='outGame';
                        $reData['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                        Gateway::sendToUid($uid,json_encode($reData));
                    }else{
                        $this->Loutgame($uid,$roomData['rid'],777);
                        $reData['m']='waitGame';
                        $reData['data']['status']=$roomData['status'];
                        $reData['data']['fitData']=$roomData['userData'][$uid]['initGameData'];
                        $reData['data']['desc']=$sddesc;
                        Gateway::sendToUid($fitid,json_encode($reData));
                        $reData['m']='outGame';
                        $reData['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                        Gateway::sendToUid($uid,json_encode($reData));
                    }
                }

            }else{
                //todo 通知对战双方 游戏继续开始

                $reData['m']='go';
                $reData['data']['desc']=$sddesc;

                $reData['data']['screen']=$screen;
                $reData['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                if($win){
                    $reData['data']['win']='1';
                }else{
                    $reData['data']['win']='0';
                }
                Gateway::sendToUid($uid,json_encode($reData));
                $reData['data']['fitData']=$roomData['userData'][$uid]['initGameData'];
                if($win){
                    $reData['data']['win']='0';
                }else{
                    $reData['data']['win']='1';
                }
                Gateway::sendToUid($fitid,json_encode($reData));
            }
        }else{
            //游戏继续...接着喊
            $this->dataModel->setPower($roomid,$uid,0);
            $this->dataModel->setPower($roomid,$fitid,1);
            $number1=$this->dataModel->setNumber1($roomid,$uid);//todo 获取喊的个数
            $number2=$this->dataModel->setNumber2($roomid,$uid);//todo 获取喊的点数
            $reData['m']='shaizi';
            $reData['n1']=$number1;
            $reData['n2']=$number2;
            Gateway::sendToUid($fitid,json_encode($reData));
        }
    }
    //init
    public function initGame(){
        $uid=$this->SocketData['uid'];//角色id
        $roomid=isset($this->SocketData['roomid'])?$this->SocketData['roomid']:0;//房间id
        $this->dataModel->updatalastTime($roomid,$uid);
        $data=$this->dataModel->getGameTwo($roomid,$uid);
        $roomData=$this->dataModel->getRoomData($roomid);
        $reData['m']='getmydata';
        $reData['data']=$data;
        $reData['vwin']=$roomData['userData'][$roomData['userData'][$uid]['fitid']]['win'];
        $reData['mwin']=$roomData['userData'][$uid]['win'];
        $reData['power']="";
        if($roomData['userData'][$uid]['isMy']=='1'){
            $reData['power']=1;
        }else{
            $reData['power']=0;
        }
        Gateway::sendToUid($uid,json_encode($reData));
    }
    //断线重连  初始化当前数据
    public function reConnInit(){
        $uid=$this->SocketData['uid'];//角色id
        $roomid=$this->SocketData['roomid'];//房间id
        $roomData=$this->dataModel->getRoomData($roomid);
        $reData['my']=$roomData['userData'][$uid];
        $reData['vs']=$roomData['userData'][$roomData['userData'][$uid]['fitid']];
        $reData['m']='reConn';
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

        $uid=$this->SocketData['uid'];
        $roomid=$this->SocketData['roomid'];//房间id
        $this->dataModel->updatalastTime($roomid,$uid);
        $roomData=$this->DataModel->getRoomData($roomid);//组信息
        $status=$roomData['status'];
        if($status!=$this->SocketData['status']){
            return;
        }
        if($status<=1){
            return;
        }

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

        foreach($roomData['userData'] as $k=>$value){
            if($roomData['userData'][$k]['out']==false){
                //判断对手为失败不操作 不失败就判断双方超时
                if($roomData['userData'][$value['fitid']]['out']==false){
                    //todo 判断出当前客户是否超时
                    if(time()-$roomData['userData'][$k]['lastTime']>91){
                        //当前客户超时啦
                        $roomData['userData'][$value['fitid']]['win']=$roomData['userData'][$value['fitid']]['win']+1;
                        $roomData['userData'][$value['fitid']]['lastTime']=time();
                        if($roomData['userData'][$value['fitid']]['win']>=2){
                            $roomData['userData'][$k]['out']=true;
                            $this->dataModel->setRoomData($roomid,$roomData);
                            $reData['m']='go';
                            Gateway::sendToUid($value['fitid'],json_encode($reData));
                        }else{
                            $roomData['userData'][$value['fitid']]['isMy']=true;
                            $this->dataModel->setRoomData($roomid,$roomData);
                            $reData['m']='go';
                            Gateway::sendToUid($value['id'],json_encode($reData));
                        }
                    }
                }
            }
        }

        //$roomData=$this->dataModel->getRoomData($roomid);
        $num=pow(2,$status);//当前组人数
        $nums=0;
        //判断是否人员全部准备齐全
        $userList=array();
        foreach ($roomData['userData'] as $k=>$v){
            //获取当前组胜利的人数
            $out=$roomData['userData'][$k]['out'];
            if($out == false){
                $nums++;
                $userList[]=$v['id'];
            }
        }
        if($nums<=$num/2){
            $this->dataModel->reRoomData($roomid);
            $reData['m']='goon';
            Gateway::sendToUid($userList,json_encode($reData));
        }
    }

    public  function timeup(){
        $uid = $this->SocketData['uid'];
        $roomid = $this->SocketData['roomid'];
        $fitid=$this->dataModel->getFitid($roomid,$uid);//获取对手id
        $mywin=$this->dataModel->getWinData($roomid,$uid);//获取胜利数

        $roomData=$this->dataModel->getRoomData($roomid);// 或许房间信息
        $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
        $status=$roomData['status'];
        $sddesc=pow(2,$status-1).'/'.pow(2,$status).'赛段';
        $screen=$roomData['userData'][$uid]['screen'];

        if($this->dataModel->setJoon($roomid,$fitid) == true){

            if($mywin==0){ //小
                $this->dataModel->setGameWin($roomid,$uid);  //胜利加一
                //  $this->dataModel->setGameXwin($roomid,$uid); //小局胜利
                //  $this->dataModel->setGameXlost($roomid,$fitid);//失败加一
                $this->dataModel->SetPermission($roomid,$fitid);// 取消权限
                $this->dataModel->GetPermission($roomid,$uid);// 获取权限
                $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
                $result['m']='go';
                $result['data']['desc']=$sddesc;
                $result['data']['win']='1';
                $result['data']['screen']=$screen;
                $result['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                Gateway::sendToUid($uid,json_encode($result));

                $result['data']['win']='0';
                $result['data']['fitData']=$roomData['userData'][$uid]['initGameData'];
                Gateway::sendToUid($fitid,json_encode($result));
            }else{  //大
                $this->dataModel->setOutData($roomid,$fitid);
                // $this->dataModel->setGameBwin($roomid,$uid); //大局胜利场次
                $this->dataModel->updatalastTime($roomid,$uid);//todo 更新最好操作时间
                if($status==1){
                    $this->curl($uid,$roomData['rid'],888);
                    $this->Loutgame($fitid,$roomData['rid'],777);
                    $result['m']='winGame';

                    $result['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                    $result['data']['desc']=$sddesc;
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($uid,json_encode($result));
                    $result['m']='outGame';
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($fitid,json_encode($result));
                }else{
                    $this->Loutgame($fitid,$roomData['rid'],777);
                    $result['m']='waitGame';
                    $result['data']['fitData']=$roomData['userData'][$fitid]['initGameData'];
                    $result['data']['desc']=$sddesc;
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($uid,json_encode($result));

                    $result['m']='outGame';
                    $result['data']['status']=$roomData['status'];
                    Gateway::sendToUid($fitid,json_encode($result));
                }
            }
            $this->dataModel->clearGameTwoData($roomid,$uid);
            $this->dataModel->clearGameTwoData($roomid,$fitid);
        }
    }
}
?>