<?PHP

class VipbuyModel extends CommonModel{

    public function vipbuy($user_id){

        $sql=D("Users");
        $where['user_id']=$user_id;
        $list=$sql->where($where)->find();
            return $list['vip'];

    }

}
?>