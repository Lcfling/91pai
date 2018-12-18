<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   控制器基类(前后台使用)
 */
defined('LMXCMS') or exit();
use Workerman\Lib\Timer;
class Action{
    protected $smarty;
    protected $config;
    public $SocketData;
    public $DataModel;
    public $MysqlModel;
    protected function __construct(){
        //lcfling   此处编写全局控制器
        //global $SocketData;
        //$this->SocketData=$GLOBALS['post'];
        /*if($this->DataModel==null){
            $this->DataModel=new DataModel();
        }*/
        if($this->MysqlModel==null){
            $this->MysqlModel=new MysqlModel();
        }
    }
    public function run($data){
        $this->SocketData=$data;
        echo var_dump($this->SocketData);
        $a=isset($this->SocketData['a']) ? $this->SocketData['a'] : 'index';
        if(method_exists($this,$a)){
            eval('$this->'.$a.'();');
        }else{
            //如果方法不存在则执行index方法
            $this->index();
        }
    }
}
?>
