<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   系统全局函数文件
 */
defined('LMXCMS') or exit();

//实体化打印数组
function print_s(){
    $arg_list = func_get_args();
    if($arg_list){
        foreach($arg_list as $v){
            echo '<pre>';
            print_r($v);
            echo '</pre>';
        }
    }
}

/* 操作系统缓存data目录下文件
 * $path:文件夹名+文件名
 * $arr:数据（有值代表保存，没值代表获取）
 * $type:true 存储  false 获取
*/
function f($path,$arr=array(),$type=false){
    if($arr || $type){
        if(is_array($arr)){
            $arr=var_export($arr,true);
        }
        $content="<?php \r\n if(!defined('LMXCMS')){exit();} \r\n //本文件为缓存文件 无需手动更改\r\n return ".$arr." \r\n?>";
        file::put(ROOT_PATH.'data/'.$path.'.php',$content);
    }else{
        if(file_exists(ROOT_PATH.'data/'.$path.'.php')){
            return require ROOT_PATH.'data/'.$path.'.php';
        }
    }
}

/* 字符串加密和解密
 * $string:要加密的字符串
 * $operation:类型，D:解密 E:加密
 * $key:密钥
 */
function encrypt($string,$operation,$key=''){
    $key = md5($key);
    $key_length = strlen($key);
    $string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
    $string_length = strlen($string);
    $rndkey = $box = array();
    $result = '';
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($key[$i % $key_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result.=chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'D') {
        if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
            return substr($result, 8);
        } else {
            return'';
        }
    } else {
        return str_replace('=', '', base64_encode($result));
    }
}
//传入字段数组，给数据进行初始化 1:post 2:get 3:data
function d(array $filed,$type = 1,$is_int=false){
    if($type == 1){
        $data = $_POST;
    }else if($type == 2){
        $data = $_GET;
    }else{
        $data = $type;
    }
    foreach($filed as $v){
        $arr[$v] = $is_int && in_array($v,$is_int) ? (int)$data[$v] : $data[$v];
    }
    return $arr;
}



//过滤非法提交信息，防止sql注入
function filter_sql(array $data){
    foreach($data as $v){
        if(is_array($v)){
            filter_sql($v);
        }else{
            //转换小写
            $v = strtolower($v);
            if(preg_match('/count|create|delete|select|update|use|drop|insert|info|from/',$v)){
                rewrite::js_back('【'.$v.'】数据非法');
            }
        }
    }
}

//mysql保留关键字，不允许使用
function mysql_retain($arr){
        $retain = array('ADD','ALL','ALTER','ANALYZE','AND','AS','ASC','ASENSITIVE','BEFORE','BETWEEN','BIGINT','BINARY','BLOB','BOTH','BY','CALL','CASCADE','CASE','CHANGE','CHAR','CHARACTER','CHECK','COLLATE','COLUMN','CONDITION','CONNECTION','CONSTRAINT','CONTINUE','CONVERT','CREATE','CROSS','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR','DATABASE','DATABASES','DAY_HOUR','DAY_MICROSECOND','DAY_MINUTE','DAY_SECOND','DEC','DECIMAL','DECLARE','DEFAULT','DELAYED','DELETE','DESC','DESCRIBE','DETERMINISTIC','DISTINCT','DISTINCTROW','DIV','DOUBLE','DROP','DUAL','EACH','ELSE','ELSEIF','ENCLOSED','ESCAPED','EXISTS','EXIT','EXPLAIN','FALSE','FETCH','FLOAT','FLOAT4','FLOAT8','FOR','FORCE','FOREIGN','FROM','FULLTEXT','GOTO','GRANT','GROUP','HAVING','HIGH_PRIORITY','HOUR_MICROSECOND','HOUR_MINUTE','HOUR_SECOND','IF','IGNORE','IN','INDEX','INFILE','INNER','INOUT','INSENSITIVE','INSERT','INT','INT1','INT2','INT3','INT4','INT8','INTEGER','INTERVAL','INTO','IS','ITERATE','JOIN','KEY','KEYS','KILL','LABEL','LEADING','LEAVE','LEFT','LIKE','LIMIT','LINEAR','LINES','LOAD','LOCALTIME','LOCALTIMESTAMP','LOCK','LONG','LONGBLOB','LONGTEXT','LOOP','LOW_PRIORITY','MATCH','MEDIUMBLOB','MEDIUMINT','MEDIUMTEXT','MIDDLEINT','MINUTE_MICROSECOND','MINUTE_SECOND','MOD','MODIFIES','NATURAL','NOT','NO_WRITE_TO_BINLOG','NULL','NUMERIC','ON','OPTIMIZE','OPTION','OPTIONALLY','OR','ORDER','OUT','OUTER','OUTFILE','PRECISION','PRIMARY','PROCEDURE','PURGE','RAID0','RANGE','READ','READS','REAL','REFERENCES','REGEXP','RELEASE','RENAME','REPEAT','REPLACE','REQUIRE','RESTRICT','RETURN','REVOKE','RIGHT','RLIKE','SCHEMA','SCHEMAS','SECOND_MICROSECOND','SELECT','SENSITIVE','SEPARATOR','SET','SHOW','SMALLINT','SPATIAL','SPECIFIC','SQL','SQLEXCEPTION','SQLSTATE','SQLWARNING','SQL_BIG_RESULT','SQL_CALC_FOUND_ROWS','SQL_SMALL_RESULT','SSL','STARTING','STRAIGHT_JOIN','TABLE','TERMINATED','THEN','TINYBLOB','TINYINT','TINYTEXT','TO','TRAILING','TRIGGER','TRUE','UNDO','UNION','UNIQUE','UNLOCK','UNSIGNED','UPDATE','USAGE','USE','USING','UTC_DATE','UTC_TIME','UTC_TIMESTAMP','VALUES','VARBINARY','VARCHAR','VARCHARACTER','VARYING','WHEN','WHERE','WHILE','WITH','WRITE','X509','XOR','YEAR_MONTH','ZEROFILL');
    foreach($arr as $v){
        if(is_array($v)){
            mysql_retain($v);
        }else{
            $v = strtoupper($v);
            if(in_array($v,$retain)){
                rewrite::js_back('【'.$v.'】为mysql保留关键字，禁止使用');
            }
        }
    }
}
//获取ip地址
function getip() {
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    }elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')){
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'),'unknown')){
        $ip = getenv('REMOTE_ADDR');
    }elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')){
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if(!preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/',$ip) && !preg_match('/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/',$ip)){
        rewrite::js_back('IP地址有误');
    }
    return $ip;
}

/* session操作
 * 如果没有$value是获取，有则是添加
 * $name:名字
 * $value:值
 */

//增加日志
function addlog($content){
    global $config;
    if($config['is_admin_log']){
        $logModel = new LogModel();
        $logModel->add($content);
    }
}
function requireClassName($classname){
    if($classname == 'Action' || $classname == 'Model'){
        $dir=ROOT_PATH.'class/';
    }else if(substr($classname,-6) == 'Action'){
        $dir=ROOT_PATH.'c/'.'index'.'/';
    }else if(substr($classname,-5) == 'Model'){
        $dir=ROOT_PATH.'m/';
    }else{
        $dir=ROOT_PATH.'class/';
    }
    if(file_exists($dir.$classname.'.class.php')){
        require_once $dir.$classname.'.class.php';
    }

}
//获取服务器操作系统类型和版本号
function getOS(){
    return php_uname('s').'&nbsp;'.php_uname('r');
}
//生成拼音 字符串 |　编码
function Pinyin($_String,$_Code='utf8'){
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha"."|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|"."cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er"."|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui"."|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang"."|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang"."|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue"."|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne"."|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen"."|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang"."|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|"."she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|"."tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu"."|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you"."|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|"."zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990"."|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725"."|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263"."|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003"."|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697"."|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211"."|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922"."|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468"."|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664"."|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407"."|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959"."|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652"."|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369"."|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128"."|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914"."|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645"."|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149"."|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087"."|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658"."|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340"."|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888"."|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585"."|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847"."|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055"."|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780"."|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274"."|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey   = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if($_Code != 'gb2312') $_String = _U2_Utf8_Gb($_String);
        $_Res = '';
        for($i=0; $i<strlen($_String); $i++)
        {
        $_P = ord(substr($_String, $i, 1));
        if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
        $_Res .= _Pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

function _Pinyin($_Num, $_Data){
    if($_Num>0 && $_Num<160) return chr($_Num);
    elseif($_Num<-20319 || $_Num>-10247) return '';
    else{
        foreach($_Data as $k=>$v){ if($v<=$_Num) break; }
        return $k;
    }
}

//拼音编码
function _U2_Utf8_Gb($_C){
    $_String = '';
    if($_C < 0x80) $_String .= $_C;
    elseif($_C < 0x800)
    {
    $_String .= chr(0xC0 | $_C>>6);
    $_String .= chr(0x80 | $_C & 0x3F);
    }elseif($_C < 0x10000){
    $_String .= chr(0xE0 | $_C>>12);
            $_String .= chr(0x80 | $_C>>6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
    } elseif($_C < 0x200000) {
    $_String .= chr(0xF0 | $_C>>18);
            $_String .= chr(0x80 | $_C>>12 & 0x3F);
            $_String .= chr(0x80 | $_C>>6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8','GB2312',$_String);
}

function _Array_Combine($_Arr1,$_Arr2){
    for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
    return $_Res;
}

//格式化推荐与热门字符串 返回数组
function formatHot($data){
    $arr = array();
    $data = str_replace(array("\r","\n"),',',$data);
    $data = preg_replace('/[,]+/',',',$data);
    $data = trim($data,',');
    $data = explode(',',$data);
    foreach($data as $k=>$v){
        $arr[$k+1] = trim($v);
    }
    return $arr;
}




//按字数截取字符串
function lmxstr($string, $length, $dot = '', $charset = 'utf-8'){
    if (strlen($string) <= $length) {
        return $string;
    }
    $strcut = '';
    if (strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t < 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    return $strcut . $dot;
}




//根据字符串提取分词
function split_str($str,$num,$split){
    ini_set('memory_limit','20M'); //设置php允许使用的内存大小，太小会导致读取词库失败
    require_once ROOT_PATH.'plug/phpanalysis/phpanalysis.class.php';
    $do_fork =  true;//岐义处理
    $do_unit =  true; //新词识别
    $do_multi =  true; //多元切分
    $do_prop =  false; //词性标注
    $pri_dict =  false; //是否预载全部词条
    PhpAnalysis::$loadInit = false; //初始化类
    $pa = new PhpAnalysis('utf-8','utf-8',$pri_dict);
    $pa->resultType = 2;
    $pa->LoadDict();//载入词典
    $pa->SetSource($str);//执行分词
    $pa->differMax = $do_multi;
    $pa->unitWord = $do_unit;
    $pa->StartAnalysis($do_fork);
    $data = $pa->GetFinallyKeywords($num,$split);
    $pa = null;
    return $data;
}




//配合采集替换转义正则  标签  关键字
function pregStr($str,$key=false){
    $str = stripcslashes($str);
    $str = preg_quote($str,'/');
    $str = str_replace(array('\*','\-'),array('[\s\S]*','-'),$str);
    if($key) $key = str_replace(array('[',']'),array('\[','\]'),$key);
    if($key) $str = str_replace($key,'([\s\S]*)',$str);
    return $str;
}


//配合array_filter去掉空数组
function removeArraySpace($v){
    if(trim($v)){
        return $v;
    }
}



function preg_callback_chinaese($e){
    return urlencode($e[0]);
}
function getFileType($filename) {
    return pathinfo($filename, PATHINFO_EXTENSION);
}
?>