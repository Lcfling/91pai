<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   系统扩展控制器基类
 */
class Data{
    static $data;
    public static function StartData($data){
        self::$data++;
        return self::$data;
    }
}
?>