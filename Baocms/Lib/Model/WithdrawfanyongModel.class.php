<?PHP

class WithdrawfanyongModel extends CommonModel{



//获取分销信息数据
//获取分销级
//匹配字符串，获取分销数额

    private $globalEdu;//分佣额度;
    private $priceAry=array();

    private $current_userid;

    private $gametype;
    private $slef_vip;
    public function fanyong($user_id,$mianis_edu,$type){
        global $current_userid,$globalEdu,$gametype,$slef_vip,$priceAry;
        $current_userid=$user_id;
        $globalEdu=$mianis_edu;
        $gametype=$type;

        $users=D('Users');
        $where['user_id']=$current_userid;
        $line_pid=$users->where($where)->find();
        $next_userid=$line_pid['pid'];//当前上级
        $slef_vip=$line_pid['vip'];//当前身份状态

       $distribution=D("distribution");
        $where['ID']='5';
        $line=$distribution->where($where)->find();
       $priceString=$line['price'];
       $priceAry = explode(",",$priceString);//分销额度存入数组

        $this->allPid($next_userid);
    }


//获取uid的所有上级
     function allPid($curId){
         $users=D('Users');
         $map['user_id']=$curId;
         $line_shangji=$users->where($map)->find();
         $next_vip=$line_shangji['vip'];
         $next_userid=$line_shangji['pid'];
        if($next_vip!=0){
            $this->addFenYong($curId);
            return;
        }
        $this->allPid($next_userid);

    }



//对应返佣额度
    private function addFenYong($curId){
        global $globalEdu,$current_userid,$gametype,$slef_vip,$priceAry;
        if($slef_vip==0){
            $newPrice=$priceAry[0];
        }else{
            $newPrice=$priceAry[1];
        }


        $p1id=$curId;
        $fanyong=D('fanyong');
        $data['fabao_id']=$current_userid;
        $data['miansi_edu']=$globalEdu;
        $data['fenyong_id']=$p1id;
        $data['fenyong_edu']=$newPrice*$globalEdu/100;
        $data['type']=$gametype;
        $data['Lv']=$len+1;
        $data['fyDate']=time();
        $fanyong->add($data);
        //佣金插入现金表
        $paid=D('Paid');
        $map['money']=$newPrice*$globalEdu/100;
        $map['user_id']=$p1id;
        $map['creatime']=time();
        $map['type']=13;
        $map['remark']='下级提现佣金到账';
        $map['is_afect']=1;
        $paid->add($map);
        //$this->addFenYong();
    }
}
?>