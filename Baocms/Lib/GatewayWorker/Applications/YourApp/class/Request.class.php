<?php

/**
 * 请求基类
 * Created by PhpStorm.
 * User: lengsad
 * Date: 2017/5/27
 * Time: 10:03
 */
class Request
{
    protected function __construct(){
        $this->ctime = time();
        $this->ip = getip();
        $this->header = $_SERVER;
        $this->data = $_POST;
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
    }

    public function run(){
        $a=isset($_GET['a']) ? $_GET['a'] : 'index';
        if(method_exists($this,$a)){
            eval('$this->'.$a.'();');
        }else{
            //如果方法不存在则执行index方法
            $this->index();
        }
    }


}