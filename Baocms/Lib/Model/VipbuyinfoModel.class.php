<?php
class VipbuyinfoModel extends CommonModel
{
//获取用户的下级通讯录
    public function buyinfo()
    {
        $map['money']=598;
        $map['vipinfo']='会员价598，会员享受额外收益（1.所有下级的提现1%收益；（非会员用户）
                             2.当下级出现了会员时，只拿该会员提现的5%，不再享受
                               该会员下级用户的提现1%；
                             3.普通用户在竞拍商品回收中享受30%回收权益，
                               会员用户享受商品回收40%的回收权益。
                             4.会员享受下级购买会员的返佣，例：A推荐B推荐C推荐D
                               ABC都是会员，D购买C得30%返佣，B得20%返佣，A得10%返佣；
                               AC是会员，B不是会员，D购买C得30%返佣，A得20%返佣；
                               购买会员的返佣只存在于会员之间，返佣三级一共为60%。）';
        return $map;
    }
}
?>