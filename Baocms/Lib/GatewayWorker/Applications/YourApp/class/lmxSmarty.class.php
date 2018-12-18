<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   smarty模板引擎单模式调用
 */
class lmxSmarty extends Smarty{
    static private $smartyObj = null;
    static function getSmarty(){
        if(self::$smartyObj == null){
            self::$smartyObj=new self();
        }
        return self::$smartyObj;
    }
    private function __clone(){}
    private function __construct(){
        $this->setConfigs();
    }
    private function setConfigs(){
        global $config;
        $this->force_compile = true; //编译文件实时清除
        $this->template_dir=$config['curr_template']; //模板路径
        $this->compile_dir=$config['smy_compile_dir'].RUN_TYPE.'/'; //编译文件路径
        $this->cache_dir=$config['smy_cache_dir'].RUN_TYPE.'/'; //缓存目录
		
        //$this->caching = false;//是否开启smarty静态缓存
        $this->left_delimiter='<{';//标签左定界符
        $this->right_delimiter='}>';//标签右定界符
    }
}
?>