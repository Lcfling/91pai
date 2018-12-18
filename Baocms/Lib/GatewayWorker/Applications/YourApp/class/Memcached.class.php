<?php

/**
 * Created by PhpStorm.
 * User: lengsad
 * Date: 2017/5/31
 * Time: 10:52
 */
defined('LMXCMS') or exit();

class Memcached
{
    function __construct($config)
    {
        $this->config = $config;
        $this->memc = new Memcache;
        $this->memc->pconnect($this->config['Memcache_host'], $this->config['Memcache_prot']);
    }

    public function set($k,$v,$e = 0){
        $r = $this->memc->set($k,$v,false,$e);
        return $r==1 ? false : true;
    }

    public function get($k){
        return $this->memc->get($k);
    }

}