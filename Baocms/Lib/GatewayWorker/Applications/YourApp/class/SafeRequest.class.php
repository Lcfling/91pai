<?php

/**
 * 请求认证类
 * Created by PhpStorm.
 * User: lengsad
 * Date: 2017/5/27
 * Time: 10:03
 */
class SafeRequest extends AuthRequest
{
    protected function __construct(){
        parent::__construct();
        $this->auth();
    }
}