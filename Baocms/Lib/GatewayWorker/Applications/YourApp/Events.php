<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
require_once ROOT_PATH."mysql-master/src/Connection.php";
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static function onWorkerStart($businessWorker)
    {

        $roomlist=array(0=>3735275);
        $redis=new Redis();
        $redis->connect('61.160.195.73', '6379');
        $redis->auth('sn2bDWI4#nw');
        $time_interval = 2.5;
        $hostUrl="http://hongbao.webziti.com/";


        //Gateway::getAllClientIdCount();
        //if($worker)
        if ($businessWorker->id == 5) {
            Timer::add(300, function()use($redis)
            {
                //echo "dingshiqi running!!!";
                file_get_contents("http://game1gao.weiquer.com/app/index/phb");
            });
        }
        if ($businessWorker->id == 4) {
            Timer::add($time_interval, function()use($redis)
            {
                //echo "dingshiqi running!!!";
                $count=Gateway::getAllUidCount();
                $redis->set('alluserIn',$count);
            });
        }
        if ($businessWorker->id == 6) {
            Timer::add(8, function()
            {
                //echo "dingshiqi running!!!";
                file_get_contents("http://game1gao.weiquer.com/app/hongbao/aotudosend?key=dbak3s7fhash34fah39t");
            });
        }
        if ($businessWorker->id == 6) {
            Timer::add(60, function()
            {
                //echo "dingshiqi running!!!";
                file_get_contents("http://game1gao.weiquer.com/app/hongbao/maotudosend?key=dbak3s7fhash34fah39t");
            });
        }
        if ($businessWorker->id == 0) {
            Timer::add($time_interval, function()
            {
                //echo "dingshiqi running!!!";
                file_get_contents("http://game1gao.weiquer.com/app/hongbao/aotuopenkick?key=dbak3s7fhash34fah39t");
            });
        }
        if ($businessWorker->id == 2) {
            Timer::add($time_interval, function()
            {
                //echo "dingshiqi running!!!";
                file_get_contents("http://game1gao.weiquer.com/app/index/gameover");
            });
        }
        if($businessWorker->id==1){
            Timer::add(5,function ()use($roomlist,$redis){
                foreach ($roomlist as $roomid){
                    echo "shshshshs".$roomid." \n";
                    $newinfo=unserialize($redis->get('game_new_room_'.$roomid));
                    //var_dump($newinfo);
                    if($redis->get('game_new_set_'.$newinfo['id'])==1){
                        continue;
                    }
                    echo "aaaas------";
                    if($newinfo['creatime']>time()-20){
                        $redis->set('game_new_set_'.$newinfo['id'],1);

                        $time=$newinfo['creatime']+20-time();

                        //开奖请求
                        Timer::add($time,function ()use ($roomid){
                            file_get_contents("http://game1gao.weiquer.com/app/game/createresult?roomid=".$roomid);
                            echo "这个是开奖请求".$roomid."\n";
                        },array(),false);

                        //结算请求
                        $time=$time+10;
                        Timer::add($time,function ()use ($roomid){
                            file_get_contents("http://game1gao.weiquer.com/app/game/balance?roomid=".$roomid);
                        },array(),false);
                        //通知庄家上庄
                        $time=$time+10;
                        Timer::add($time,function ()use ($roomid,$newinfo){
                            $newinfo['out_number']=explode(' ',$newinfo['out_number']);
                            $data=array(
                                'roomid'=>$roomid,
                                'm'=>'isgoon',
                                'data'=>$newinfo,
                            );
                            $data=json_encode($data);
                            Gateway::sendToAll($data);
                        },array(),false);
                        //判断庄家是否上庄
                        $time=$time+10;
                        Timer::add($time,function ()use ($roomid){
                            file_get_contents("http://game1gao.weiquer.com/app/game/isgoon?roomid=".$roomid);
                        },array(),false);
                    }
                }
            });
        }
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     *
     */
    //private $Model="";

    public static function onConnect($client_id) {
        // 向当前client_id发送数据
        //echo "content success";
        //$reData="slogin";
        //Gateway::sendToClient($client_id, "Hello $client_id\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\n");
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message) {
       // 向所有人发送
       //数据解析
       /*$a=array(
           "m"=>"index",
           "a"=>"index",
           "uid"=>51,
           'client_id'=>$client_id
       );*/
       //$message=json_encode($a,true);
       //以上调试时候使用
       $SocketData=json_decode($message,true);
       $SocketData['client_id']=$client_id;

       //$GLOBALS['post']=$SocketData;

       //Gateway::joinGroup($client_id);
       spl_autoload_register('requireClassName');
//加载类文件函数


//单入口
       //$SocketData=$GLOBALS['post'];

       $extendEnt = 'Action';
       $m=isset($SocketData['m']) ? ucfirst(strtolower($SocketData['m'])) : 'Index';
       echo $m.$extendEnt."--------------------------------\n";
       if(!class_exists($m.$extendEnt)){ $m = 'Index'; }
       echo $m.$extendEnt."--------------------------------\n";
       eval('$action=new '.$m.$extendEnt.'();');
       eval('$action->run($SocketData);');
       //Gateway::sendToAll("$client_id said $message");
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout");
   }
}
