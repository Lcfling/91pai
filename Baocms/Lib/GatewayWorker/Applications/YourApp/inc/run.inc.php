<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   系统初始化
 */
defined('LMXCMS') or exit();




spl_autoload_register('requireClassName');
//加载类文件函数

//把加载类文件函数注册到autoload中


//如果前台访问模式为伪静态，则解析url地址  暂时不支持扩展插件的伪静态，如果需要，请自己更改，因为涉及到一些url顺序问题，如果自己需要请根据下面的方法来修改，地址将变成 http://xxx.com/expend/项目文件夹名/m/a/?? 这样的地址，扩展插件的 运行类型是 extend ，判断下即可


//单入口
$SocketData=$GLOBALS['post'];

$extendEnt = 'Action';
$m=isset($SocketData['m']) ? ucfirst(strtolower($SocketData['m'])) : 'Index';
if(!class_exists($m.$extendEnt)){ $m = 'Index'; }
echo $m.$extendEnt."--------------------------------\n";
eval('$action=new '.$m.$extendEnt.'();');
eval('$action->run();');
?>