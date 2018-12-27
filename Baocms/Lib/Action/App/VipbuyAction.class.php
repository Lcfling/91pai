<?php
require_once LIB_PATH.'/GatewayClient/Gateway.php';

use GatewayClient\Gateway;

class VipbuyAction extends CommonAction
{
    public function vipbuy()
    {
        $d=D("Vipbuy");
        $data=$d->vipbuy($this->uid);
        $this->ajaxReturn($data,'购买会员状态');
    }
}
?>