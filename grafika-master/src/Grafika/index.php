<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/21
 * Time: 16:59
 */


use Grafika\Grafika;
require_once dirname(__FILE__)."/Grafika.php";
//die();
//die(dirname(__FILE__)."/Grafika.php");
$editor = Grafika::createEditor();
$editor->open($image1 , './tghaibao/bg1.jpg');
$editor->open($image2 , './tghaibao/bg2.jpg');
$editor->blend ( $image1, $image2 , 'normal', 0.9, 'center');
$editor->save($image1,'333.jpg');

