<?php

class VipbuyinfoAction extends  CommonAction{

    public function buyinfo(){
        $d=D("Vipbuyinfo");
        $data=$d->buyinfo();
        $this->ajaxReturn($data,'描述');
    }
}
?>