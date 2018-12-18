<?php

/**
 * 请求认证类
 * Created by PhpStorm.
 * User: lengsad
 * Date: 2017/5/27
 * Time: 10:03
 */
class AuthRequest extends Request
{
    protected function __construct(){
        parent::__construct();
        global $config;
        $this->mem = new Memcached($config);
    }

    function auth(){
        if(!$this->header['ACCOUNT']){
            print_r($this->header);
            die("ACCOUNT error:".$this->header['ACCOUNT']);
        }
        if($this->header['token'] != $this->getToken() || !$this->getToken()){
            die("TOKEN error:".$this->header['token']);
        }
    }

    function newToken(){
        global $config;
        $token = string::pwdmd5($this->data['username'].$this->data['password'].$config['liveTokenExtKey']);
        $this->keyName = string::pwdmd5($this->data['username']);
        return $token;
    }

    function getToken(){
        global $config;
        return $this->mem->get($config['mem_key_head'].string::pwdmd5($this->data['username']));;
    }

}