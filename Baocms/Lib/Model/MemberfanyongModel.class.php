<?PHP

class MemberfanyongModel extends CommonModel{



//获取分销信息数据
//获取分销级
//匹配字符串，获取分销数额
    private $numLevel;
    private $globalEdu;//会员充值额度;
    private $priceAry=array();
    private $vipAry=array();
    private $priceString;
    private $current_userid;
    private $len;
    private $pidAry=array();
    private $gametype;


    public function fanyong($user_id,$mianis_edu,$type){
        //先判断自身 是否 是vip 不是 则不执行
        $selfSql=D("Users");
        $where['user_id']=$user_id;
        $slef=$selfSql->where($where)->find();
        if($slef["vip"]==0){
        return;
        }

        global $current_userid,$globalEdu,$gametype;
        $current_userid=$user_id;
        $globalEdu=$mianis_edu;
        $gametype=$type;
        $distribution=D("distribution");
        $where['ID']='4';
        $line=$distribution->where($where)->find();

        global $numLevel;
        $numLevel=$line['numRen'];
        global $len;
        $len=$numLevel;

        $priceString=$line['price'];
        global $priceAry;
        $priceAry = explode(",",$priceString);//分销额度存入数组

        $this->allPid($current_userid);

    }


//获取uid的所有vip上级
     function allPid($curId){

        global $len;

        if($len==0){

            $this->filter();//过滤无效的pid
            return;
        }else{
            $len--;
        }

        $users=D('Users');
        $where['user_id']=$curId;
        $line_pid=$users->where($where)->find();

        $next_userid=$line_pid["pid"];//当前上级ID
         global $pidAry;
         array_push($this->pidAry,$next_userid);

         //当前上级ID 身份状态
         $map['user_id']=$next_userid;
         $line_vip=$users->where($map)->find();
         $next_user_vip=$line_vip["vip"];//当前上级身份
         global $vipAry;
         array_push($this->vipAry,$next_user_vip);
         //----------------------
        $this->allPid($next_userid);

    }
//过滤pid
public function filter(){
global $pidAry,$vipAry;
    //获取真实vip的数组长度
    $vipAryLen=count($this->vipAry);
for($i=0;$i<$vipAryLen;$i++){
    if($this->vipAry[$i]==0){
        //移除对应的pid数组
        unset($this->pidAry[$i]);
    }

}
$this->pidAry=array_values($this->pidAry);
$this->addFenYong();//数据保存

}

//对应返佣额度
    private function addFenYong(){

        global $len,$pidAry,$numLevel,$priceAry,$globalEdu,$current_userid,$gametype;
        //获取真实pid的数组长度

        $pidAryLen=count($this->pidAry);

        if ($pidAryLen==$len){
            return;
        }


        $newPrice=$priceAry[$len];
        $p1id=$this->pidAry[$len];

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
        $map['remark']='下级购买会员佣金到账';
        $map['is_afect']=1;
        $paid->add($map);
        $len++;
        $this->addFenYong();
    }
}
?>