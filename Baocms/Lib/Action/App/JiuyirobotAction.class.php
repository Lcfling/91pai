<?php
class JiuyirobotAction extends Action
{
    /**参拍机器人
     * @param goods_id 商品id
     * @param strike_price 成交价格
     * @param auction_money 参拍金额
     * @return
     */
//    public function inrobotuidredis(){
//        Cac()->del('jiuyi_robot_uid');
//       $robotuids = D('Users')->where(array('is_robot'=>1))->select();
//       foreach ($robotuids as $v){
//           Cac()->rPush('jiuyi_robot_uid',$v['user_id']);
//       }
//       $data =  Cac()->lRange('jiuyi_robot_uid',0,-1);
//       print_r($data);
//    }
    /**参拍机器人
     * @param goods_id 商品id
     * @param strike_price 成交价格
     * @param auction_money 参拍金额
     * @return
     */
    public function jprobot(){
        $token = $_GET['token'];
        if($token == 'c827fb7f6c4b68288710451765306864'){
            $robotrule =  D('Jiuyirobotrule')->where(array('open'=>1))->select();
            foreach ($robotrule as $v){
                //取出一个机器人id
                $robotid = $this->getrobotuid();
                if($robotid ==0){
                    $this->ajaxReturn('','机器人id为空!',0);
                }
                $goods_id = $v['goods_id'];
                $goodsdata = unserialize(Cac()->get('jiuyi_auction_'.$goods_id));
                $auction_money =($goodsdata['strike_price'] - $goodsdata['auction_price'])/$goodsdata['auction_num'];
                $min = $v['min'];
                $max = $v['max'];
                $this->auctiongoods($robotid,$goods_id,$auction_money,$min,$max);
                //已抢过的机器人id再存入最后
                $this->inrobotuid($robotid);
            }
        }else{
            die('蛇皮，让你蛇皮!');
        }


    }
    /**参拍商品
     * @param goods_id 商品id
     * @param strike_price 成交价格
     * @param auction_money 参拍金额
     * @return
     */
    private function auctiongoods($uid,$goods_id,$auction_money,$min,$max){
        $users =   D('Users');
        $jiuyi = D('Jiuyi');
       //竞拍商品是否存在
        $goodsdata = D('Jiuyi')->soldoutcheck($goods_id);
        if($goodsdata['sold_out']== 0){
            $info['type']=1;
            $info['remark']='商品已下架!';
            $this->ajaxReturn('','商品已下架!',0);
        }
        //获得随机期数
        $periods_id = $this->getperiodid($goods_id,$goodsdata['creatime']);
        if(empty($periods_id)){
            $this->ajaxReturn('','商品不存在!',0);
        }
        //竞拍商品是否已被竞拍
        $auctionstatus = $jiuyi->auctioncheck($periods_id);
        if($auctionstatus==2){
            $data['type']=2;
            $data['remark']='竞拍不存在!';
            $data['periods_id']=$periods_id;
            $this->ajaxReturn($data,'竞拍不存在!',0);
        }elseif($auctionstatus==1){
            $info['type']=3;
            $info['remark']='已被竞拍!';
            $this->ajaxReturn('','已被竞拍!',0);
        }
        //将事先每期产品生成的几次能竞拍成功的次数列表第一个数给取出出队
        $periodsnum=$jiuyi->getperiodsnum($periods_id);//竞拍金额
        if($auction_money != $periodsnum){
            $info['type']=5;
            $info['remark']='参拍金额不对!';
            $this->ajaxReturn('','参拍金额不对!',0);
        }
        $num = Cac()->lLen('jiuyi_auction_list_'.$periods_id);
        if($num >=$min&& $num <=$max){

            //参拍入队
            $jiuyi->UserQueue($uid,$periods_id);
            //竞拍入paid表
            $users->addmoney($uid, $auction_money, 91, 1, "机器人参拍");
            //竞拍记录入库
            $jiuyi->setauctionrecorddata($uid,$periods_id,$auction_money,0);
            $data['type']=1;
            $data['remark']='参拍成功!';
            $this->ajaxReturn($data,'参拍成功!',1);

        }
    }

    /**10s随机期数算法
     * @param goods_id 商品id
     * @return
     */
    private function getperiodid($goods_id,$creatime){
        $auctiongoods = D('Periods')->where(array('goods_id'=>$goods_id,'is_auction'=>0))->field('id')->select();
        $daytime = $this->msectime();
        $auctiontime = ceil(($daytime - $creatime)/10000);
        $remainder = $auctiontime%count($auctiongoods);
        return  $auctiongoods[$remainder]['id'];
    }
    /**返回当前的毫秒时间戳
     *
     */
    private function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**从队列中取出一个机器人id   出队

     * @return $robotid 0 或  大于0
     *
     */
    private function getrobotuid(){
        $robotid=Cac()->lPop('jiuyi_robot_uid');
        return $robotid;
    }
    /**将已领取的机器人id存入最后
     * @param $robotid
     */
    private function inrobotuid($robotid){
        Cac()->rPush('jiuyi_robot_uid',$robotid);

    }
}