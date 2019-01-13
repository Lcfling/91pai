<?php
require_once LIB_PATH.'/GatewayClient/Gateway.php';

use GatewayClient\Gateway;

class SzwwTimerAction extends Action{
    /**胜者为王机器人存入缓存
     * @param
     * @return
     */
    public function inrobotuidredis(){
//        Cac()->del('szww_robot_uid');
//       $robotuids = D('Users')->where(array('is_robot'=>1))->select();
//       foreach ($robotuids as $v){
//           Cac()->rPush('szww_robot_uid',$v['user_id']);
//       }
//
//        $data =Cac()->lRange('szww_robot_uid',0,-1);
//        print_r($data);

    }
    /**胜者为王机器人规则更新
     * @param
     * @return
     */
    public function robotsave(){
//        Cac()->del('szww_robot_num');
//        $timecha =(int)$_POST['timecha'];
//        $open =(int)$_POST['open'];
//        $robotsave = D('Szwwrobotrule')->where(array('id'=>1))->save(array('timecha'=>$timecha,'open'=>$open,'creatime'=>time()));
//        if($robotsave){
//            Cac()->rPush('szww_robot_num',$timecha);
//        }
//        $data =Cac()->lRange('szww_robot_num',0,-1);
//        print_r($data);

    }

    /**自动给庄家领取红包 结算发给所有人
     * @param roomid 房间号
     * @param token 安全验证
     * @return
     */
    public function  zjgethongbao(){
       $data =  $_GET;
       if($data['token']=='3acf16259def65456fc2a68ab5e10d96'){
           //echo "进入定时器";exit();
           $szwwsend = D("Szwwsend");
           //获取该房间未结束的红包
            $unfinishedlist =  $szwwsend->where(array("roomid"=>$data['roomid'],'is_freeze'=>0))->select();

            foreach ($unfinishedlist as $k=>$v){
                $timecha=time() - 60;
                if($v['is_over']==1 || $v['creatime']<$timecha){
                    $hbinfo = unserialize(Cac()->get("szww_send_".$v['id']));

                    //设置mysql红包为领取状态为完毕并改庄家解冻状态
                    $hbfinishedstatus =  $this->unfreeze($v['id']);
                    // 庄家发的红包的剩余金额和冻结金额的返还
                    $this->zjmoneyback($hbinfo);

                    //返佣扣除庄家赢得金额，结算金额入库
                    $this->zjfypay($hbinfo);

                    //闲家赔付入库
                    $this->xjfypay($hbinfo);
                    //将领取的结算结果发送给房间所有人
                    $szwwsend->hbgetlistnotify($hbinfo);

                    //如果庄家没有领取红包自动领包并更改已领取红包缓存
                    $szwwsend->savezjkickstatus($hbinfo['id'],$hbinfo['user_id']);

                    //记录日志
                    file_put_contents('./hbstatus.log',"红包结束定时器执行状态（1 成功 0 失败）：大红包id：".$v['id']."状态：".$hbfinishedstatus.PHP_EOL,FILE_APPEND);

                }

            }
       }else{
           echo "未进入定时器";

       }

    }
    /**胜者为王机器人开始发包
     * @param $money 发的钱
     * @param $num 红包数量
     * @param $user_id 用户id
     * @param $creatime 创建时间
     * @param $roomid 房间号ssssss
     */
    public function szwwrobotsend(){

        $szwwsend = D("Szwwsend");
        $usermodel =D('Users');
        //$enres =  $this->AESEncryptRequest('abcd','szww');
        //xenRosorxXQ8WA+YdEwX1w==
        //post 或者get过来的数据需要处理一下，防止base64加密乱码
        $datas =  $encodedData = str_replace(' ','+',$_GET['code']);;
        //aes解密
        $enres =$this->AESDecryptResponse('abcd',$datas);
        if($enres == 'szww'){
            $robotdata =D('Szwwrobotrule')->where(array('open'=>1))->find();
            if($robotdata){
                $sendstatus =Cac()->lLen('szww_robot_num');
                $todaytime = time();
                $sendtime = $robotdata['timecha']*$sendstatus + $robotdata['creatime'];
                if($todaytime >=$sendtime){
                    //每次新增一个用于计算时间
                    Cac()->rPush('szww_robot_num',$robotdata['timecha']);
                    //取出一个机器人id
                    $robotid = $this->getrobotuid();
                    if($robotid ==0){
                        $this->ajaxReturn('','机器人id为空!',0);
                    }
                    //取出机器人id从新存入最后
                    $this->inrobotuid($robotid);
                    //money 以分为单位 所以获取到要*100
                    $money = rand($robotdata['smoney'],$robotdata['bmoney'])*100;
                    $num = 4;
                    $roomid = '3735278';
                    //红包金额
                    $hongbaomoney = 88*$num;
                    //冻结金额
                    $freezemoney = $money*($num-1);
                    //加锁
                    $nostr=time().rand_string(6,1);
                    if(!$szwwsend->qsendbaoLock($robotid,$nostr)){
                        $this->ajaxReturn('','频繁操作',0);
                    }
                    $roomData=D('Room')->getRoomData($roomid);
                    if(empty($roomData)){
                        $szwwsend->opensendbaoLock($robotid);
                        $this->ajaxReturn('','房间不存在!',0);
                    }

                    //生成红包
                    $hongbao_info=$this->createhongbao($money,$hongbaomoney,$num,$roomid,$robotid);
                    if($hongbao_info){
                        //解锁
                        $szwwsend->opensendbaoLock($robotid);
                        //将发红包的冻结金额存表
                        D('Users')->reducemoney($robotid,$hongbaomoney,70,1,'机器人发送红包（胜者）');
                        D('Users')->reducemoney($robotid,$freezemoney,71,1,'机器人发包冻结（胜者）');
                        $useinfo = $usermodel->getUserByUid($robotid);
                        //通知
                        $this->sendnotify($hongbao_info,$useinfo);
                        $this->ajaxReturn('','发送完毕!',1);
                    }else{
                        $szwwsend->opensendbaoLock($robotid);
                        $this->ajaxReturn('','红包发送失败！',0);
                    }
                }else{
                    $this->ajaxReturn('','未到时间',0);
                }
            }else{
                $this->ajaxReturn('','机器人关闭',0);
            }
        }else{
            die('蛇皮，让你蛇皮！');
        }

    }
    /**发包调用
     * @param $money 红包金额
     * @param $uid 用户id
     * @param $roomid 房间号
     * @param $num 红包数量
     */
    private function createhongbao($money,$hongbaomoney,$num,$roomid,$uid){
        $token=md5('szww_'.genRandomString(6).time().$uid);

        $data=array();
        $data['token']=$token;
        $data['money']=$money;
        $data['num']=$num;
        $data['roomid']=$roomid;
        $data['user_id']=$uid;
        $data['is_over']=0;
        $data['overtime']=0;
        $data['creatime']=time();
        D('Szwwsend')->add($data);//大红包添加完毕

        //取出红包加入缓存
        $hongbao_info=D('Szwwsend')->where(array('token'=>$token))->find();

        if(empty($hongbao_info)){
            return false;
        }

        //将大红包存入redis
        Cac()->set('szww_send_'.$hongbao_info['id'],serialize($hongbao_info));
        //根据金额
        $kickarr=$this->getkicklist($hongbaomoney,$num);
        //获取数组第二大
        $second_key = $this->di_er_da($kickarr);
        //小红包入库
        foreach($kickarr as $k=>$value){
            if($k==$second_key){
                $data['user_id']=0;
                $data["hb_id"]=$hongbao_info['id'];
                $data["is_banker"]=1;//是否是庄家
                $data["is_robot"]=1;//是否是机器人？
                $data["is_receive"]=0;//是否已经领取
                $data["money"]=$value;
                $data['recivetime']=time();
                $data["creatime"]=time();
                D('szwwget')->add($data);

            }else{
                $data['user_id']=0;
                $data["hb_id"]=$hongbao_info['id'];
                $data["is_banker"]=0;//是否是庄家
                $data["is_robot"]=1;
                $data["is_receive"]=0;
                $data["money"]=$value;
                $data['recivetime']=0;
                $data["creatime"]=time();
                D('szwwget')->add($data);
            }
        }
        //获取小红包
        $new_kicklist=D('szwwget')->where(array('hb_id'=>$hongbao_info['id']))->select();

        foreach ($new_kicklist as $k=>$v){
            if($v['is_banker']==0){
                Cac()->rPush('szwwget_queue_'.$hongbao_info['id'],$v['id']);
                Cac()->rPush('szwwget_queue_back_'.$hongbao_info['id'],$v['id']);//复制一条队列  用于遍历数据
                Cac()->set('szwwget_id_'.$v['id'],serialize($v));
            }else{
                Cac()->lPush('szwwget_queue_back_'.$hongbao_info['id'],$v['id']);//复制一条队列  用于遍历数据
                Cac()->set('szwwget_id_'.$v['id'],serialize($v));
            }

        }

        $len=Cac()->lLen('szwwget_queue_'.$hongbao_info['id']);
        if($len==$num-1){
            return $hongbao_info;
        }else{
            return false;
        }

    }

    /**获取数组第二大的key
     * @param $arr
     * @return false|int|string
     */
    function di_er_da($arr){
        $arr = array_diff($arr,array(max($arr)));
        $second_key = array_search(max($arr),$arr);
        return $second_key;
    }

    /**红包分几个小包
     * @param $money 总钱数 单位：分
     * @param $num 小包数量
     * @return $money_arr 数组
     */
    private function getkicklist($money,$num){
        $totle=$money;
        if($num>1){
            $nums_arr=array();

            while (count($nums_arr)<$num-1){
                $point=rand(1,$totle-1);
                while(in_array($point,$nums_arr)){
                    $point=rand(1,$totle-1);
                }
                $nums_arr[]=$point;
            }
            arsort($nums_arr);
        }else{
            $nums_arr[]=0;
        }
        $maxkey=$totle;
        $money_arr=array();
        foreach($nums_arr as $k=>$value){
            $money_arr[]=$maxkey-$value;
            $maxkey=$value;
        }
        if($num>1){
            $money_arr[]=$maxkey;
        }
        return $money_arr;
    }
    /**胜者为王机器人发包全局通知
     * @param $hb 大红包信息
     * @param $userinfo 用户信息

     */
    private function sendnotify($hb,$userinfo)
    {

        Gateway::$registerAddress = '116.140.34.55:1238';
        //Gateway::$registerAddress = '127.0.0.1:1238';
        if($userinfo['face']==''){
            $userinfo['face']="img/avatar.png";
        }
        $data=array(
            'roomid'=>$hb['roomid'],
            'm'=>5,
            'data'=>array(
                'username'=>$userinfo['nickname'],
                'user_id'=>$userinfo['user_id'],
                'avatar'=>$userinfo['face'],
                'hongbao_id'=>$hb['id'],
                'money'=>$hb['money'],
                'overtime'=>null,
                'creatime'=>$hb['creatime'],
                'num'=>$hb['num'],
                'token'=>$hb['token'],
                'is_over'=>0,
                'is_freeze'=>0,
            )
        );
        $data=json_encode($data);
        Gateway::sendToAll($data);
    }



    /**庄家发的红包的剩余金额和冻结金额的返还
     * @param $hbinfo 大红包信息
     * @return
     *
     */
    private function zjmoneyback($hbinfo){

        $szwwsend = D("Szwwsend");
        $users =   D('Users');
        $money = $szwwsend->zjpaymoney($hbinfo);
        //file_put_contents('./token.txt','money'.$money.PHP_EOL,FILE_APPEND);
        $users->addmoney($hbinfo['user_id'],$money[2],73,$is_afect=1,'发包解冻（胜者）',$order_id=0);
        if($money[1] > 0){
            $users->addmoney($hbinfo['user_id'],$money[1],72,$is_afect=1,'发包返还（胜者）',$order_id=0);
        }

    }
    /**返佣扣除庄家赢得金额
     * @param $hb 红包信息
     */
    private function zjfypay($hb){
        $szwwget = D('Szwwget');
        $szwwfy =   D('Szwwfy');
        $users =   D('Users');
        $hb_id = $hb['id'];
        $uid =$hb['user_id'];
        $getmoneytotal = $szwwget->where("hb_id = $hb_id and user_id > 0")->sum('paymoney');
        if($getmoneytotal!=0){
            //结算庄家金额
            $users->addmoney($hb['user_id'],-$getmoneytotal,74,$is_afect=1,'结算（胜者）',$order_id=0);
        }

        if(-$getmoneytotal>0){
            //玩家盈利抽取5%
            $fymoney =-$getmoneytotal * 0.05;

            //扣除庄家赢得钱
            $users->reducemoney($uid,$fymoney,81,$is_afect=1,'盈利扣除（胜者）',$order_id=0);
            //闲家的返佣
            $szwwfy->fanyong($uid,$fymoney,'szww');

        }
    }
    /**返佣扣除庄家赢得金额
     * @param $hb 红包信息
     */
    private function xjfypay($hb){
        $szwwfy =   D('Szwwfy');
        $users =   D('Users');
        $hb_id = $hb['id'];
        $type = '8';
        $zjuid =$hb['user_id'];
        $remark='金额赔付（胜者）';
        $uids = Cac()->lRange('szwwback_user_'.$hb_id,0,-1);

        foreach ($uids as $v){
           // print_r($v);
            if($v != $zjuid){
                $money= Cac()->get('szww_paymoney_'.$hb_id.$v);

                //闲家抢包解冻
                $users->addmoney($v,$hb['money'],73,$is_afect=1,'抢包解冻（胜者）',$order_id=0);
                //闲家赔付记录表
                $users->addmoney($v,$money,$type,$is_afect=1,$remark,$order_id=0);
                if($money>0){
                    //闲家盈利抽取5%
                    $fymoney = $money*0.05;

                    //扣除闲家赢得钱
                    $users->reducemoney($v,$fymoney,81,$is_afect=1,'盈利扣除（胜者）',$order_id=0);
                    //闲家的返佣
                    $szwwfy->fanyong($v,$fymoney,'szww');
                }
            }


        }


    }

    /**庄家金额1分钟之后进行解冻 红包领取结束
     * @param $hbinfo 大红包信息
     * @return
     *
     */
    public function unfreeze($hongbao_id){
        $szwwsend = D("Szwwsend");

        $data=array('is_over'=>1,'overtime'=>time(),'is_freeze'=>1);
        $savestatus = $szwwsend->where(array('id'=>$hongbao_id))->save($data);
        $hongbao_info=$szwwsend->getInfoById($hongbao_id);
        Cac()->set('szww_send_'.$hongbao_info['id'],serialize($hongbao_info));
        return $savestatus;

    }
    /**从队列中取出一个机器人id   出队

     * @return $robotid 0 或  大于0
     *
     */
    private function getrobotuid(){

        $robotid=Cac()->lPop('szww_robot_uid');
        return $robotid;
    }
    /**将已领取的机器人id存入最后
     * @param $robotid
     */
    private function inrobotuid($robotid){
        Cac()->rPush('szww_robot_uid',$robotid);

    }


    /**
     * 通过AES加密请求数据
     *
     * @param array $query
     * @return string
     */
    private function AESEncryptRequest($encryptKey, $query){
        return $this->encrypt_pass($query,$encryptKey);

    }
    // 加密
    private function encrypt_pass($input, $key) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $input = $this->pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = '0102030405060708';
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }
    //填充
    private function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * 通过AES解密请求数据
     *
     * @param array $query
     * @return string
     */
    private function AESDecryptResponse($encryptKey,$data){
        return $this->decrypt_pass($data,$encryptKey);
    }
    // 解密
    private function decrypt_pass($sStr, $sKey) {

        $iv = '0102030405060708';
        $decrypted= mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $sKey,
            base64_decode($sStr),
            MCRYPT_MODE_CBC,
            $iv
        );
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }



}




?>