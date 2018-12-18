<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/21
 * Time: 17:26
 */
<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/7/2
 * Time: 上午11:28
 */
class PaymentModel{

    public function creatorder($paydata){
        $order_sn=time().rand(10000,99999);
        $orderData['order_sn']=$order_sn;
        $orderData['type']=$paydata['type'];
        $orderData['pay_type']=$paydata['pay_type'];
        $orderData['uid']=$paydata['uid'];
        $orderData['viplevel']=$paydata['viplevel']?$paydata['viplevel']:0;
        $orderData['userid']=$paydata['userid']?$paydata['userid']:0;
        $orderData['month']=$paydata['month']?$paydata['month']:0;
        $orderData['goods_is']=$paydata['goods_id']?$paydata['goods_id']:0;
        $orderData['address_id']=$paydata['address_id']?$paydata['address_id']:0;
        $orderData['totle_money']=$paydata['totle_money'];
        $orderData['paidmoney']=0;
        $orderData['creattime']=time();
        $orderData['paytime']=0;
        $orderData['status']=0;

        $orderModel=D('Order');

        $orderid=$orderModel->add($orderData);
        if($orderid>0){
            return $order_sn;
        }else{
            return false;
        }

    }
    public function payorder($order_sn){
        require_once ADDON_PATH."/library/Paypal.php";
        $orderModel=D('Order');
        $orderData=$orderModel->where("order_sn=".$order_sn)->find();
        if(!empty($orderData)){
            $payhandle=new $orderData['pay_type'];
            $res=$payhandle->payorder($orderData);
        }

    }
    public function notify($order_sn){
        require_once ADDON_PATH."/library/Paypal.php";
        $orderModel=D('Order');
        $orderData=$orderModel->where("order_sn=".$order_sn)->find();
        if(!empty($orderData)){
            $payhandle=new $orderData['pay_type'];
            $res=$payhandle->notify($orderData);
            if($res){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }
    public function paiddown($order_sn){
        //获取订单数据
        $orderModel=D('Order');
        $userModel=D('Order');
        $orderData=$orderModel->where("order_sn=".$order_sn)->find();
        $userData=$userModel->where("uid=".$orderData['uid'])->find();
        //改变订单
        $data['status']=1;
        $data['paytime']=time();
        $data['paidmoney']=$orderData['totle_money'];
        $orderModel->where("order_sn=".$order_sn)->save($data);
        //1余额添加 2会员升级
        if($orderData['type']==1){
            $data['money']=$userData['money']+$orderData['totle_money'];
            $userModel->where("uid=".$orderData['uid'])->save($data);
            $accdata['money']=$orderData['totle_money'];
            $accdata['uid']=$orderData['uid'];
            $accdata['type']=1;
            $accdata['time']=time();
            $accdata['remark']="余额充值";
            D('AccountLog')->add($accdata);
        }else if($orderData['type']==2){
            //会员升级 分两种 为自己  为朋友
            $this->vippaid($orderData);
        }
    }
    public function vippaid($data){
        //30天 2592000秒
        $userData=model('User')->getUserInfo($data['userid']);
        if($userData['vip']==1){

        }
        if($userData['vipovertime']<time()){
            $overtime=2592000*$data['month']+time();
        }else{
            if($data['viplevel']>$userData['vip']){
                $oadd=(int)($userData['vipovertime']-time()/4);
                $overtime=time()+$oadd+2592000*$data['month'];
            }else{
                $overtime=$userData['vipovertime']+2592000*$data['month'];
            }
        }
        $updata['vip']=$data['viplevel'];
        $updata['vipovertime']=$overtime;
        model('User')->where('uid='.$data['userid'])->save($updata);




        if($data['uid']!=$data['userid']){
            //入库一条 赠送数据
            $sendData['uid']=$data['uid'];
            $sendData['type']=4;
            $sendData['touid']=$data['userid'];
            $sendData['status']=1;
            $sendData['time']=time();
            $sendID=D('SendLogs')->add($sendData);
        }
        //入库消费记录
        $payData['uid']=$this->uid;
        $payData['type']=2;
        $payData['money']=$data['totle_money'];
        if(isset($sendID) && $sendID>0){
            $payData['log_id']=$sendID;
        }else{
            $payData['log_id']=0;
        }
        $payData['touid']=$data['userid'];
        $payData['giftid']=0;
        $payData['puid']=$userData['puid'];
        $payData['time']=time();
        $payData['remark']="礼物赠送";
        D('PayLog')->add($payData);

        if($data['paytype']=='gold'){
            $accountData['money']=-$data['totle_money'];
            $accountData['uid']=$data['uid'];
            $accountData['type']=5;
            $accountData['time']=time();
            $accountData['remark']="升级会员";
            D('AccountLog')->add($accountData);
        }
    }
}