<?php
class JiuyiAction extends CommonAction
{

    public function aaaa(){


    }

    /**竞拍展示
     * @param
     * @return
     */
    public function showauctiongoods(){

      $data  =  D('Jiuyi')->showauction();

      $this->ajaxReturn($data,'请求成功',1);
    }

    /**立即拍商品检测 查看商品是否下架
     * @param goods_id 商品id
     * @return $goodsdata 商品详情
     */
    public function auctiongoodscheck(){

        $goods_id = (int)$_POST['goods_id'];
        //竞拍商品是否存在
        $goodsdata = D('Jiuyi')->soldoutcheck($goods_id);

        if($goodsdata){
            $auction_money = ($goodsdata['strike_price'] - $goodsdata['auction_price'])/$goodsdata['auction_num'];

            $auctiondetail['auction_money'] = $auction_money;
            $auctiondetail['todaytime']=$this->msectime();
            $auctiondetail['goodsdata'] = $goodsdata;
            $this->ajaxReturn($auctiondetail,'商品存在!',1);
        }else{
            $this->ajaxReturn('','商品不存在!',0);
        }

    }
    /**参拍商品
     * @param goods_id 商品id
     * @param strike_price 成交价格
     * @param auction_money 参拍金额
     * @return
     */
    public function auctiongoods(){
        $users =   D('Users');
        $jiuyi = D('Jiuyi');
        $goods_id = (int)$_POST['goods_id'];
        $strike_price = (int)$_POST['strike_price']*100;
        $auction_money = $_POST['auction_money']*100;

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
        //查看是否余额不足
        $userMoney=$users->getUserMoney($this->uid);
        if($userMoney<$strike_price){
            $info['type']=4;
            $info['remark']='余额不足!';
            $this->ajaxReturn('','余额不足!',0);

        }
        //将事先每期产品生成的几次能竞拍成功的次数列表第一个数给取出出队
        $periodsnum=$jiuyi->getperiodsnum($periods_id);//竞拍金额
        //print_r($auction_money);
        if($auction_money != $periodsnum || $auction_money<=0){
            $info['type']=5;
            $info['remark']='竞拍金额不对!';
            $this->ajaxReturn('','竞拍金额不对!',0);
        }
        if($periodsnum>0){

            //参拍入队
            $jiuyi->UserQueue($this->uid,$periods_id);
            //竞拍入paid表
            $users->addmoney($this->uid, $auction_money, 91, 1, "参拍金额");
            //用户参拍佣金扣除
            $users->reducemoney($this->uid,$auction_money*0.2,94,1,"参拍盈利扣除");
            //用户佣金上级返佣
            D('Auctionfanyong')->fanyong($this->uid,$auction_money*0.2*0.5,"91参拍");
            if($jiuyi->is_lastauction($periods_id)){
                //竞拍记录入库
                $jiuyi->setauctionrecorddata($this->uid,$periods_id,$auction_money,1);
                //扣用户金额
                $users->reducemoney($this->uid,$strike_price,92,1,"竞拍成功");
                //生成一个新的期数
                $jiuyi->createauction($periods_id);
                //更改竞拍状态
                $jiuyi->saveauctionstatus($this->uid,$periods_id);
                //生成订单
                //$jiuyi->createorder($this->uid,$periods_id);
                $data['type']=2;
                $data['remark']='竞拍成功!';
                $this->ajaxReturn($data,'竞拍成功!',1);
            }else{
                //竞拍记录入库
                $jiuyi->setauctionrecorddata($this->uid,$periods_id,$auction_money,0);
                $data['type']=1;
                $data['remark']='参拍成功!';
                $this->ajaxReturn($data,'参拍成功!',1);
            }
        }else{
            $this->ajaxReturn('','手慢了，已被竞拍!',0);
        }
    }

    /**商品添加
     * @param periods_id 期数id
     * @param strike_price 成交价格
     * @param auction_money 每次竞拍金额
     * @return
     */
    public function addgoods(){

        //创建库存-3
        $data['goods_name']='测试商品';$data['goods_header']='测试商品测试商品测试商品测试商品测试商品';$data['goods_img']='图片';$data['strike_price']='15000';$data['auction_price']='5000';
        $data['buyback_price']='2000';$data['auction_num']='10';$data['inventory_num']='100';$data['sold_out']='1';$data['creatime']=time();
        //D('Jiuyi')->creategoods($data);
        print_r(unserialize(Cac()->get('jiuyi_auction_78')));
//        print_r(Cac()->get('jiuyi_periods_num_43'));
//        print_r(Cac()->lrange('jiuyi_auction_list_175',0,-1));
    }
    /**竞拍成功商品期数展示详细
     * @param goods_id 商品id
     * @return
     */
    public function showperiodsdetails(){
        $users =  D('Users');
        $goods_id = (int)$_POST['goods_id'];
        $data =array();
        $periodsnum = Cac()->lLen('jiuyi_auction_periods_'.$goods_id);
        if($periodsnum>0){
            //取出最近5条竞拍记录
            $periodslist = Cac()->lRange('jiuyi_auction_periods_'.$goods_id,-5,-1);
            foreach ($periodslist as $v){

                $auctionrecordlist = D('Auctionrecord')->where(array('periods_id'=>$v))->order('creatime desc')->select();

                foreach ($auctionrecordlist as &$k ){
                    $uid = $k['user_id'];
                    $userinfo = $users->getUserByUid($uid);
                    $k['face']=$userinfo['face'];
                    $k['nickname']=$userinfo['nickname'];
                    if($k['face']==""){
                        $k['face']="img/avatar.png";
                    }
                }
                //获取该期数的商品竞拍详情
                $periodslist  =unserialize(Cac()->get('jiuyi_auction_success_'.$v));

                //获取商品详情
                $goodslist =unserialize(Cac()->get('jiuyi_auction_'.$goods_id)) ;
                $auctionlist = array(
                    'goods_name'=>$goodslist['goods_name'],
                    'goods_header'=>$goodslist['goods_header'],
                    'goods_img'=>$goodslist['goods_img'],
                    'strike_price'=>$goodslist['strike_price'],
                    'auction_price'=>$goodslist['auction_price'],
                    'periods_num'=>$periodslist['periods_num'],
                    'creatime'=>date('Y/m/d/H:i:s',$periodslist['creatime']),
                    'auction_time'=>date('Y/m/d/H:i:s',$periodslist['auction_time']),
                    'list'=>$auctionrecordlist
                );
                $data[]=$auctionlist;
            }
            $data = array_reverse($data);
            $this->ajaxReturn($data,'请求成功!',1);
        }else{
            $this->ajaxReturn('','暂无记录!',0);
        }

    }
    /**10s随机期数算法
     * @param goods_id 商品id
     * @return
     */
    private function getperiodid($goods_id,$creatime){
        $auctiongoods = D('Periods')->where(array('goods_id'=>$goods_id,'is_auction'=>0))->field('id')->select();
        //print_r($auctiongoods);
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

    /**查看用户余额
     *
     */
    public function getUserMoney() {
        $usermoney =  D('Users')->getUserMoney($this->uid);
        $this->ajaxReturn($usermoney,'请求成功!',1);
    }

    /**查看竞拍记录之收益情况
     *
     */
    public function checkauctionproceeds() {
        //收益价格 期数 产品 回购或者发货情况 时间
        $uid = $this->uid;
        $_GET['p']=(int)$_POST['p'];
        import('ORG.Util.Page'); // 导入分页类
        //竞拍里面的数据
        $count = D('Auctionrecord')->where("user_id = $uid")->count();

        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数

        //竞拍里面的数据
        $auctionrecord = D('Auctionrecord a')
            ->join('bao_periods b on a.periods_id = b.id ')
            ->join('bao_goods c on b.goods_id = c.id')
            ->where("a.user_id = $uid")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('creatime desc')
            ->field('a.auction_money,b.periods_num,c.goods_name,a.creatime')
            ->select();
        //重新修改数组数据
        $auctionrecorddata = array_map(function($value){
            $value['creatime'] = date('m/d/H:i',$value['creatime']);
            return $data[] =$value;}, $auctionrecord);
//        //期数里面的数据
//        $periodsdata = D('Periods a')
//            ->join('bao_goods b on a.goods_id = b.id')
//            ->where("a.user_id = $uid and a.is_auction = 1")
//            ->field('b.strike_price,a.periods_num,b.goods_name,a.creatime,a.ship_status')
//            ->select();
//        $periodsdatas = array_map(function($value){
//            $value['money'] = -$value['strike_price'];
//            $value['ship_status'] = 0;
//            $value['creatime'] = date('Y/m/d/H:i',$value['creatime']);
//            return $data[] =$value;}, $periodsdata);
//        //期数回购的数据
//        $huigoudata = D('Periods a')
//            ->join('bao_goods b on a.goods_id = b.id')
//            ->where("a.user_id = $uid and a.ship_status = 1")
//            ->field('b.buyback_price,a.periods_num,b.goods_name,a.creatime,a.ship_status')
//            ->select();
//        $huigoudatas = array_map(function($value){
//            $value['money'] = $value['buyback_price'];
//            $value['creatime'] = date('Y/m/d/H:i',$value['creatime']);
//            return $data[] =$value;}, $huigoudata);
//        $datalist = array_merge($auctionrecorddata,$periodsdatas,$huigoudatas);
//        $creatime = array_column($datalist, 'creatime');//将creatime的值单独拿出来
//        array_multisort($creatime,SORT_DESC,$datalist );//多维数组的排序


        $data['current']=$Page->currentPage();
        $data['list']=$auctionrecorddata;
        $this->ajaxReturn($data,'请求成功!',1);

    }
    /**查看竞拍记录之产品
     *
     */
    public  function showProduct(){
        $uid = $this->uid;//用户id
        $_GET['p']=(int)$_POST['p'];
        import('ORG.Util.Page'); // 导入分页类
        //竞拍里面的数据
        $count = D('Periods')->where("user_id = $uid")->count();

        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        //期数里面的数据
        $periodsdata = D('Periods a')
            ->join('bao_goods b on a.goods_id = b.id')
            ->where("a.user_id = $uid and a.is_auction = 1")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('creatime desc')
            ->field('a.periods_num,b.goods_name,a.ship_status,a.creatime')
            ->select();
        $periodsdatas = array_map(function($value){
            $value['creatime'] = date('m/d/H:i',$value['creatime']);
            return $data[] =$value;}, $periodsdata);
        $data['current']=$Page->currentPage();
        $data['list']=$periodsdatas;
        $this->ajaxReturn($data,'请求成功!',1);
    }


    /**背包展示
     *
     */
    public function showbick() {
        //收益价格 期数 产品 回购或者发货情况 时间
        $huigou = D('Huigou_record');//回购表
        $fahuo = D('Fahuo_record');//发货表
        $periods =  D('Periods');//期数管理表
        $users =   D('Users');
        $userperiods = $periods->where(array('user_id'=>$this->uid,'is_auction'=>1))->order('auction_time desc')->select();
        if($userperiods){
            foreach ($userperiods as &$v){
                if($v['ship_status'] == 0 ){
                    $goodsdata = unserialize(Cac()->get('jiuyi_auction_'.$v['goods_id'])) ;
                    //获取用户信息
                    $userinfo = $users->getUserByUid($this->uid);
                    if($userinfo['vip']==1){
                        $goodsdata['buyback_price'] = $goodsdata['buyback_price'];
                    }else{
                        $goodsdata['buyback_price'] = $goodsdata['buyback_price_no'];
                    }
                    $v['list'] = $goodsdata;
                }elseif ($v['ship_status'] == 1){
                    $huigoudata =$huigou->where(array('periods_id'=>$v['id'],'user_id'=>$this->uid))->find();
                    $v['list'] = $huigoudata;
                }elseif($v['ship_status'] == 2 ||$v['ship_status'] == 3){
                    $fahuodata = $fahuo->where(array('periods_id'=>$v['id'],'user_id'=>$this->uid))->find();
                    $v['list'] = $fahuodata;
                }
            }
            $this->ajaxReturn($userperiods,'请求成功!',1);
        }else{
            $this->ajaxReturn('','暂无数据!',0);
        }

    }
    /**背包回购
     * @param buyback_price 回购价格
     * @param periods_id 期数id
     */
    public function kickbuyback(){
        if($_POST){
            $buybackmoney = (int)$_POST['buyback_price'];
            $periods_id = (int)$_POST['periods_id'];
            $users =   D('Users');
            $huigou =D('Huigou_record');
            $jiuyi = D('Jiuyi');
            //获取该期数的商品竞拍详情
            $periodslist  =unserialize(Cac()->get('jiuyi_auction_success_'.$periods_id));
            //获取商品的信息
            $goodsdata = unserialize(Cac()->get('jiuyi_auction_'.$periodslist['goods_id'])) ;


            $data=array(
                'user_id'=>$this->uid,
                'periods_id'=>$periods_id,
                'goods_id'=>$periodslist['goods_id'],
                'goods_name'=>$goodsdata['goods_name'],
                'goods_header'=>$goodsdata['goods_header'],
                'goods_img'=>$goodsdata['goods_img'],
                'money'=>$buybackmoney,
                'creatime'=>time()
            );
            //存入回购表
            $huigoustatus =  $huigou->add($data);
            if($huigoustatus){
                //更改期数表状态
                $jiuyi->saveperiods($periodslist,1);
                //回购金额入paid表
                $users->addmoney($this->uid, $buybackmoney, 93, 1, "回购金额");

                $this->ajaxReturn('','回购成功!',1);
            }else{
                $this->ajaxReturn('','回购失败!',0);
            }
        }else{
            $this->ajaxReturn('','回购失败!',0);
        }


    }
    /**背包发货
     * @param periods_id 期数id
     * @param name 收货人姓名
     * @param mobile 收货人联系方式
     * @param ship_site 收货人地址
     */
    public function kickshipments(){
        if($_POST){
            $periods_id = (int)$_POST['periods_id'];
            $name = $_POST['name'];
            $mobile = $_POST['mobile'];
            $ship_site = $_POST['ship_site'];
            $fahuo =D('Fahuo_record');
            $jiuyi = D('Jiuyi');
            if(empty($name)){
                $this->ajaxReturn('','收货人姓名不能为空!',0);
            }
            if(empty($mobile)){
                $this->ajaxReturn('','手机号码不能为空!',0);
            }
            if(empty($ship_site)){
                $this->ajaxReturn('','收货人地址不能为空!',0);
            }
            //获取该期数的商品竞拍详情
            $periodslist  =unserialize(Cac()->get('jiuyi_auction_success_'.$periods_id));
            //print_r($periodslist);
            //获取商品的信息
            $goodsdata = unserialize(Cac()->get('jiuyi_auction_'.$periodslist['goods_id'])) ;

            $data=array(
                'user_id'=>$this->uid,
                'periods_id'=>$periods_id,
                'goods_id'=>$periodslist['goods_id'],
                'goods_name'=>$goodsdata['goods_name'],
                'goods_header'=>$goodsdata['goods_header'],
                'goods_img'=>$goodsdata['goods_img'],
                'name'=>$name,
                'mobile'=>$mobile,
                'ship_site'=>$ship_site,
                'tracking_no'=>0,
                'creatime'=>time()
            );
            //print_r($data);
            //存入回购表
            $huigoustatus =  $fahuo->add($data);
            if($huigoustatus){
                //更改期数表状态
                $jiuyi->saveperiods($periodslist,2);

                $this->ajaxReturn('','提交成功!',1);
            }else{
                $this->ajaxReturn('','提交失败!',0);
            }
        }else{
            $this->ajaxReturn('','提交失败!',0);
        }


    }




}
?>