<?php /**
 *  【梦想cms】 http://www.lmxcms.com *
 *
 *   不需要验证连接token的  直接继承Action   不在继承HomeAction
 *
 *   匹配控制器index
 *
 *   返回的数据格式
 *   array(
 * m=index
 *     data=array(
 *
 * )
 * )
 *
 */
defined('LMXCMS') or exit();
use Workerman\Lib\Timer;
use GatewayWorker\lib\Gateway;
class IndexAction extends HomeAction{
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        echo 'no do';
    }
    /*todo 匹配方法
     *
     * 接收客户发送的json匹配数据
     * 加入临时房间
     *
     * 判断   满员->开车  未满员-> 返回当前匹配数据
     *
     * 开车逻辑：临时房间加入到正式房间并初始化数据
     * 返回客户数据 通知客户进入房间
    */
    public function pipei(){

    }


}

?>