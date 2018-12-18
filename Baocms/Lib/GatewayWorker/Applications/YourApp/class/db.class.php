<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   数据库连接
 */
defined('LMXCMS') or exit();
class db{
    static private $conn=null;
    static protected function getConn(){
        if(self::$conn == null){
            self::$conn=new self();
        }
        return self::$conn;
    }
    private function __clone(){}
    private function __construct() {
        $port=DB_PORT;
        $localhost = $port ? DB_HOST.':'.$port : DB_HOST;
        $link=@mysql_connect($localhost,DB_USER,DB_PWD);
        if(!$link){
            exit('数据库连接错误'.mysql_error());
        }
        @mysql_select_db(DB_NAME,$link) or die('没有这个数据库'.mysql_error());
        mysql_query('SET NAMES '.DB_CHAR);
    }
    
    //查询记录数
    protected function countDB($tab,$param){
        $We = $this->where($param);
        $sql="SELECT count(1) FROM ".DB_PRE."$tab $We";
        //echo $sql;
        $result=$this->query($sql);
        $data = mysql_fetch_row($result);
        $this->result($result);
        return $data['0'];
    }
    
    
    //join $join=表和字段的数组  $param=其他参数的数组
    /*array( //join参数
     *      'fromTab' => 'from 表名',
     *      'field' => array('表名' => array('字段名')),
     *      'ON' => array(array('left | right ? join type','on tabname','条件')//一次join一个数组，条件全部写一起 用 and 连接条件),
     * )
     * $param = array( //其他条件参数 where 后面的
     *      'where' => array('条件','条件','...'),
     *      'limit' => array(),
     * )
     */
    protected function joinDB($join,$param){
        //遍历表和字段
        foreach($join['field'] as $fieldKey=>$field){
            foreach($field as $v){
                $allField[]=DB_PRE.$fieldKey.'.'.$v;
            }
        }
        //遍历join类型和条件
        foreach($join['on'] as $wc => $w){
            //初始化条件
            $b = explode('and',$w[2]);
            foreach($b as $k=>$v){
                preg_match('/\[(.*)\]/',$v,$match);
                $sign = $match[1];
                $c = trim($v);
                $e = explode($sign,$c);
                foreach($e as $s){
                    $x =  str_replace(array('[',']'),'',$s);
                    $y[$wc][$k][] = DB_PRE.trim($x);
                }
                $arr[$wc][$k]= implode(' '.$sign.' ',$y[$wc][$k]);
                $on[$wc]=$w[0]." JOIN ".DB_PRE.$w[1].' ON '.implode(' and ', $arr[$wc]);
            }
            
        }
        $on = implode(' ',$on);
        $allField = implode(',',$allField);
        $we = $this->where($param);
        $sql="SELECT $allField FROM ".DB_PRE.$join['fromTab']." $on $we";
        $result=$this->query($sql);
        while(!!$a=mysql_fetch_assoc($result)){
            $data[]=$a;
        }
        $this->result($result);
        return $data ? $data : array();
    }
    //查询
    protected function selectDB($tab,Array $field,$param=array()){
        $arr = array();
        $field = implode(',',$field);
        $force = '';
        //强制进入某个索引
        if($param['force']) $force = ' force index('.$param['force'].')';
        if($param['ignore']) $force = ' ignore index('.$param['ignore'].')';
        $sqlStr = $this->where($param);
        $sql="SELECT $field FROM ".DB_PRE."$tab$force $sqlStr";
        //echo $sql;
        //exit();
        $result=$this->query($sql);
        while(!!$a=mysql_fetch_assoc($result)){
            $arr[]=$a;
        }
        $this->result($result);
        //print_r($att);
        return $arr;

    }
    
    //删除
    protected function deleteDB($tab,$param=array()){
        if($param){
            //条件
            if($param['where']){
                $We = $this->where($param);
                $sql="DELETE FROM ".DB_PRE."$tab $We";
                return mysql_query($sql);
            }
        }
    }
    
    //查询一条数据
    protected function oneDB($tab,Array $field,Array $param){
        $field = implode(',',$field);
        $force = '';
        //强制进入某个索引
        if($param['force']) $force = ' force index('.$param['force'].')';
        if($param['ignore']) $force = ' ignore index('.$param['ignore'].')';
        $We = $this->where($param);
        $sql="SELECT ".$field." FROM ".DB_PRE."$tab$force $We limit 1";
        //echo $sql.'</br>';
        $result=$this->query($sql);
        $data = mysql_fetch_assoc($result);
        return $data ? $data : array();
    }
    
    //更新
    protected function updateDB($tab,Array $updateData,Array $param){
        foreach($updateData as $key=>$v){
            $uD[]="$key='$v'";
        }
        $We = $this->where($param);
        if(!$We){
            return;
        }
        $ud=implode(',',$uD);
        $sql="UPDATE ".DB_PRE."$tab SET $ud $We";
        //echo $sql;
        return $this->query($sql);
    }
    //增加
    protected function addDB($tab,$data){
        foreach($data as $key=>$v){
           $field[]=$key;
           $value[]="'$v'";
        }
        $field = implode(',',$field);
        $value = implode(",",$value);
        $sql="INSERT INTO ".DB_PRE."$tab($field) VALUES($value)";
        //echo $sql;
        //exit();
        $this->query($sql);
        return mysql_insert_id();
    }
    //执行sql
    public function query($sql){
        $query=mysql_query($sql);
        if(!$query){
            exit('sql语句有误'.mysql_error());
        }
        return $query;
    }
    //清理结果集
    private function result($result){
        mysql_free_result($result);
    }
    
    protected function queryDb($sql){
        return $this->query($sql);
    }
    
    /*
     * $param  条件数组
     * array(
     *      '条件名字' => array('条件参数','条件参数','...') ? 参数,
     * )
     */
    protected function where($param){
        $We=$limit=$order=$like='';
        //判断参数是否存在
        if($param){
            //条件
            if($param['where']){
                if(is_array($param['where'])){
                    foreach($param['where'] as $v){
                        $We[]=$v;
                    }
                    $We =  ' WHERE '.implode(' AND ',$We);
                }else{
                    $We = ' WHERE '.$param['where'];
                }
            }
            //数量
            if($param['limit']){
                $limit=' LIMIT '.$param['limit'];
            }
            //排序
            if($param['order']){
                $order=' ORDER BY '.$param['order'];
            }
            //搜索
            if($param['like']){
                $like = $param['where'] ? ' AND '.$param['like'] : ' WHERE '.$param['like'];
            }
        }
        return $We.' '.$like.' '.$order.' '.$limit;
    }
    
    //创建数据表
    protected function createDB($tabname,array $fileArr,$charset,$engine){
        $fileStr = implode(',',$fileArr);
        $sql = "CREATE TABLE ".DB_PRE."$tabname(";
        $sql .= $fileStr;
        $sql .= ")ENGINE=$engine DEFAULT CHARSET=$charset;";
        return $this->query($sql);
    }
    
    //增加字段
    protected function fieldDB($tabname,$type,$fieldname,$default=''){
        if($default){
            $default = " DEFAULT '$default'";
        }
        $sql = "ALTER TABLE `".DB_PRE."$tabname` ADD `$fieldname` $type NOT NULL$default";
        return $this->query($sql);
    }
    
    //删除字段
    protected function delFieldDB($tab,$fieldName){
        $sql = "ALTER TABLE `".DB_PRE."$tab` DROP `$fieldName`";
        return $this->query($sql);
    }
    
    //删除数据表
    protected function delTabDB($tabname){
        $sql = "DROP TABLE `".DB_PRE."$tabname`";
        return $this->query($sql);
    }
    
    //返回数据库中所有表名
    protected function getAllTabDb(){
        $sql = "show tables";
        $result=$this->query($sql);
        while(!!$a=mysql_fetch_row($result)){
            $arr[]=$a[0];
        }
        $this->result($result);
        return $arr;
    }
    //根据自定义sql语句查询
    protected function sqlDb($sql){
        $result=$this->query($sql);
        while(!!$a=mysql_fetch_assoc($result)){
            $arr[]=$a;
        }
        $this->result($result);
        return $arr;
    }
    
}
?>