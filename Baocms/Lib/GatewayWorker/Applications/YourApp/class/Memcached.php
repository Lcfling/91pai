<?php

/**
 * Created by PhpStorm.
 * User: lengsad
 * Date: 2017/5/31
 * Time: 10:52
 */
class Memcached
{
    function __construct($config)
    {
        $this->config = $config;
        if(!$this->connect){
            $this->connect = $this->newConnect();
        }
    }

    private function newConnect()
    {
        $mem = new Memcache;
        $mem->pconnect($this->config['Memcache_host'], $this->config['Memcache_prot']);
        return $mem;
    }

    public function add($k, $v, $e=0){
        return $this->connect->add($k, $v, false, $e);
    }

    publci function get($k){
        echo $k;
    }

}