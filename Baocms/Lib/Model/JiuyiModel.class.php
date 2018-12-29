<?php
/**
 * jiuyi_auction +商品id  该商品id的商品详情
 * jiuyi_auction_success +商品id  该商品的竞拍情况
 * jiuyi_auction_list +期数id       该期数的商品抢购队列（用来解决竞拍的并发）
 *
 */

require_once LIB_PATH.'/GatewayClient/Gateway.php';

use GatewayClient\Gateway;

class JiuyiModel extends CommonModel{

    /**竞拍展示
     * @param
     * @return $auctionlist 竞拍商品数组
     */
    public function showauction(){

        $auctionlist  =   D('Goods')->where(array('sold_out'=>1))->select();
        Cac()->set('jiuyi_auctionlist',$auctionlist);
        return $auctionlist;
    }
    /**竞拍商品下架测试
     * @param goods_id 商品id
     * @return false or 商品详情
     */
    public function soldoutcheck($goods_id){

       $goods= unserialize(Cac()->get('jiuyi_auction_'.$goods_id));
       //print_r($goods);
       if($goods){
           return $goods;
       }else{
           $goodsdata = D('Goods')->where(array('id'=>$goods_id))->find();
           if($goodsdata){
               Cac()->set('jiuyi_auction_'.$goods_id,serialize($goodsdata));
               return $goodsdata;
           }else{
               return false;
           }
       }

    }

    /**竞拍商品是否已被成功竞拍
     * @param $periods_id 期数id
     * @return 0 未被竞拍 1 已被竞拍   2  竞拍不存在
     */
    public function auctioncheck($periods_id){
        $status =0;
        $periodsdata=  Cac()->get('jiuyi_auction_success_'.$periods_id);
        if($periodsdata){
            if($periodsdata['is_auction']== 1){
                $status = 1;
                return $status;
            }else{
                return $status;
            }
        }else{
            $perioddata = D('Periods')->where(array('id'=>$periods_id))->find();
            if($perioddata){
                Cac()->set('jiuyi_auction_success_'.$periods_id,serialize($perioddata));
                if($periodsdata['is_auction']== 1){
                    $status = 1;
                    return $status;
                }else{
                    return $status;
                }
            }else{
                $status = 2;
                return $status;
            }

        }
    }



    /**从队列中取出一个数   出队
     *
     * @param $periods_id
     *
     * @return $periodsnum 0 或  大于0
     *
     */
    public function getperiodsnum($periods_id){
        $periodsnum=Cac()->lPop('jiuyi_auction_list_'.$periods_id);
        return $periodsnum;
    }


    /**参拍完成之后 入队已经参拍
     * @param $periods_id
     * @param $uid
     *
     */
    public function UserQueue($uid,$periods_id){
        Cac()->rPush('jiuyi_auction_user_'.$periods_id,$uid);
    }
    /**竞拍记录入库
     * @param $uid  用户id
     * @param $periods_id 期数id
     * @param $auction_money 竞拍金额
     * @return
     */
    public function setauctionrecorddata($uid,$periods_id,$auction_money,$is_auction){
        $data['user_id']=$uid;
        $data['periods_id']=$periods_id;
        $data['auction_money']=$auction_money;
        $data['is_auction'] =$is_auction;
        $data['creatime']=time();
        D('Auctionrecord')->add($data);
    }

    /**判断是否是最后一个竞拍
     * @param periods_id 期数id
     * @return bool
     */

    public function is_lastauction($periods_id){
        $num = Cac()->lLen('jiuyi_auction_list_'.$periods_id);
        if($num == 0){
            return true;
        }else{
            return false;
        }
    }

    /**更改竞拍状态
     * @param $uid 用户id
     * @param periods_id 期数id
     */

    public function saveauctionstatus($uid,$periods_id){
        $data =unserialize(Cac()->get('jiuyi_auction_success_'.$periods_id));
        $data['user_id'] = $uid;
        $data['is_auction'] = 1;
        $data['auction_time'] = time();
        D('Periods')->where(array('id'=>$periods_id))->field('user_id,is_auction,auction_time')->save($data);
        Cac()->set('jiuyi_auction_success_'.$periods_id,serialize($data));
        Cac()->rPush('jiuyi_auction_periods_'.$data['goods_id'],$periods_id);
    }

    /**生成一个新的期数 并查看库存，库存-1
     * @param periods_id 期数id
     * @return
     */

    public function createauction($periods_id){
        $data =unserialize(Cac()->get('jiuyi_auction_success_'.$periods_id)) ;
        //print_r($data);
        $goodsdata =unserialize(Cac()->get('jiuyi_auction_'.$data['goods_id']));
        $auction_money = ($goodsdata['strike_price'] - $goodsdata['auction_price'])/$goodsdata['auction_num'];
        $Periods = D('Periods');
        $goods =D('Goods');
        unset($data['id']);
        //if($goodsdata['inventory_num']>=1){
            //获取该商品的最大期数
            $num = Cac()->get('jiuyi_periods_num_'.$data['goods_id']);
            $periods_maxnum = $num + 1 ;
            Cac()->set('jiuyi_periods_num_'.$data['goods_id'],$periods_maxnum);
            $data['periods_num']=$periods_maxnum;
            $data['creatime']=time();
            $periodsid = $Periods->add($data);
            //该期数的商品抢购队列（用来解决竞拍的并发）
            for($j = 0;$j<$goodsdata['auction_num'];$j++){
                Cac()->rPush('jiuyi_auction_list_'.$periodsid,$auction_money);
            }
            $goodsdata['inventory_num'] = $goodsdata['inventory_num']-1;
            $goods->where(array('id'=>$data['goods_id']))->field('inventory_num')->save($goodsdata);
            Cac()->set('jiuyi_auction_'.$data['goods_id'],serialize($goodsdata));
        //}

    }

    /**生成订单
     * @param $uid 用户id
     * @param periods_id 期数id
     * @return bool
     */

    public function createorder($uid,$periods_id){
        $jlorder =D('Jlorder');
        $order_no = $this->build_order_no();
        $serial_no = date('YmdHis').uniqid();
        $data=array(
            'user_id'=>$uid,
            'periods_id'=>$periods_id,
            'order_no'=>$order_no,
            'serial_no'=>$serial_no,
            'is_ship'=>0,
            'ship_time'=>0,
            'creatime'=>time()
        );
       $orderid =  $jlorder->add($data);
       $data['id']= $orderid;
       Cac()->set('jiuyi_jlorder_'.$periods_id,serialize($data));
    }

    /**
     * 得到新订单号
     * @return  string
     */
    public function build_order_no()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        /* JY + 年月日 + 6位随机数 */
        return 'JY' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
    /**
     * 得到新流水单号 16位
     * @return  string
     */
    private function build_serial_no(){
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    /**创建商品
    商品表：goods
    商品id   id
    商品名称  goods_name
    商品标题  goods_header
    商品图 goods_img
    成交价strike_price
    竞拍价auction_price
    竞拍次数auction_num
    库存inventory_num
    是否上架sold_out  默认0  1 上架
    创建时间 creatime
     * @return
     */
    public function creategoods($data)
    {
        $goods_id =  D('Goods')->add($data);

        Cac()->set('jiuyi_auction_'.$goods_id,serialize($data));
        Cac()->set('jiuyi_periods_num_'.$goods_id,3);
        $auction_money = ($data['strike_price'] - $data['auction_price'])/$data['auction_num'];

        $newperiods = array(
            'goods_id'=>$goods_id,
            'user_id'=>0,
            'is_auction'=>0,
            'creatime'=>time()
        );
        //生成3期产品
        for($i = 1;$i<4;$i++){
            $newperiods['periods_num']=$i;
            $periodsid= D('Periods')->add($newperiods);

            //该期数的商品抢购队列（用来解决竞拍的并发）
            for($j = 0;$j<$data['auction_num'];$j++){
                Cac()->rPush('jiuyi_auction_list_'.$periodsid,$auction_money);
            }
        }
    }

    /**更改期数表
     *
     */
    public function saveperiods($periodslist,$status){
        $periods =  D('Periods');//期数管理表
        //更改期数表
        $periods->where(array('id'=>$periodslist['id']))->field('ship_status')->save(array('ship_status'=>$status));
        //更改期数缓存
        $periodslist['ship_status']=$status;
        Cac()->set('jiuyi_auction_success_'.$periodslist['id'],serialize($periodslist));
    }



}




?>