<?php
require_once LIB_PATH.'/GatewayClient/Gateway.php';

use GatewayClient\Gateway;

class MyteamAction extends CommonAction
{
    public function countcommission(){

        $d=D("Myteam");
        $num=$d->countcommission($this->uid);
        $this->ajaxReturn($num,'团队总人数');
    }
    public function directpushnum(){
        $d=D("Myteam");
        $conut=$d->directpushNum($this->uid);
        $this->ajaxReturn($conut,'直推人数');
    }

    public function directpushinfo(){
        $d=D("Myteam");
        $conut=$d->directpushinfo($this->uid);
        $this->ajaxReturn($conut,'直推人列表信息');
    }

    public function vipteammoneyinfo(){
        $d=D("Myteam");
        $data=$d->vipteammoneyinfo($this->uid);
        $this->ajaxReturn($data,'购买会员佣金信息');
    }

    public function rebateinfo(){
        $d=D("Myteam");
        $data=$d->rebateinfo($this->uid);
        $this->ajaxReturn($data,'佣金信息');
    }

}
?>