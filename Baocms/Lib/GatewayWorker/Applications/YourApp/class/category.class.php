<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   栏目数据、模型数据处理类
 */
defined('LMXCMS') or exit();
class category{
    
    //获取全部栏目数组
    public static function allclass(){
	$data = f('public/class');
        return $data ? $data : array();
    }
    
    //获取模型全部数组
    public static function allmodule(){
	$data = f('public/module');
        return $data ? $data : array();
    }
    
    //按照栏目重新组合一维数组  源数组  级别   比较id  比较条件 字段1|字段2
    public static function oneLevel($data,$level=0,$id=0,$field='uid|classid'){
       $arr=array();
       $newfield = explode('|',$field);
       if($data && is_array($data)){
            foreach($data as $v){
                if($v[$newfield[0]] == $id){
                    $v['signLevel']=$level;
                    $arr[]=$v;
                    $arr = array_merge($arr,self::oneLevel($data,$level+1,$v[$newfield[1]]));
                }
            }
        }
       return $arr;
    }
    //根据classid返回栏目的所有子栏目  一维数组  $isclassid 是否包含本身
    public static function getClassChild($classid,$isclassid=false){
        $arr = array();
        foreach($GLOBALS['allclass'] as $v){
            if($v['uid'] == $classid){
               $arr[] = $v;
               $arr = array_merge($arr,self::getClassChild($v['classid']));
            }
        }
        if($isclassid){
            return array_merge($arr,array($GLOBALS['allclass'][$classid]));
        }else{
            return $arr;
        }
    }
    
    //组合返回多维数组  源数组 classid
    public static function categoryMore(array $data,$classid=0){
        $arr = array();
        if($data && is_array($data)){
            foreach($data as $v){
                if($v['uid'] == $classid){
                    $v['child'] = self::categoryMore($data,$v['classid']);
                    $arr[] = $v;
                }
            }
        }
        return $arr;
    }
    
    //根据栏目id返回所在位置数组
    public static function navposArr($classid){
        $arr = array();
        $parentid = $GLOBALS['allclass'][$classid]['uid'];
        if($parentid != 0){
            foreach($GLOBALS['allclass'] as $v){
                if($v['classid'] == $parentid){
                    $arr[] = $v;
                    if($v['uid'] != 0){
                        $arr = array_merge(self::navposArr($v['classid']),$arr);
                    }
                }
            }
        }
        return $arr;
    }
    
    //根据mid返回字段数组  $mid 空为全部，否则为对应的
    public static function getField($mid=false){
        $allField = f('public/field');
        if($mid){
            return $allField[$mid] ? $allField[$mid] : array();
        }else{
            return $allField ? $allField : array();
        }
    }
    
    //获取栏目分级数组 select $is：是否只要普通栏目
    public  static function classSelect($is = false){
        //获取栏目数组
        $classdata = self::oneLevel($GLOBALS['allclass']);
        $str='&nbsp;&nbsp;';
        //格式化数据
        foreach($classdata as $k => $v){
            //if($is && $v['classtype'] != 0) continue;
            // What the fuck's it @lengsad
            if($v['classtype'] == 1 || $v['classtype'] == 2) continue;
            $arr='';
            if($v['signLevel'] > 0){
                for($i=$k+1;$i<count($classdata);$i++){
                    $arr[] = $classdata[$i];
                }
                $split = '└';
				if($arr){
					foreach($arr as $value){
						if($v['uid'] == $value['uid']){
							$split = '├';
							break;
						}
					}
				}
                $v['html'] = str_repeat($str,$v['signLevel']).$split;
                
            }
            if($v['classtype'] == 1 || $v['classtype'] == 2){
                foreach($classdata  as $getPath){
                    if($v['uid'] == $getPath['classid']){
                        $classpath = $getPath['classpath'];
                        break;
                    }else{
                        $classpath = '/';
                    }
                }
                $v['classpath'] = $classpath;
            }

            $newClassData[] = $v;
        }
        return $newClassData;
    }
    
    
    //注入栏目和父栏目变量
    public static function assign_class($classData,$parentData,&$smarty){
        $smarty->assign('classid',$classData['classid']);
        $smarty->assign('classname',$classData['classname']);
        $smarty->assign('classimage',$classData['images']);
        $smarty->assign('classurl',classurl($classData['classid']));
        $smarty->assign('parent_classid',$parentData['classid']);
        $smarty->assign('parent_classname',$parentData['classname']);
        $smarty->assign('parent_classurl',classurl($parentData['classid']));
        $smarty->assign('parent_classimage',$parentData['images']);
        if($parentData['classtype'] == 1){
            $columnModel = new ColumnModel();
            $smarty->assign('parent_classcontent',string::html_char_dec($columnModel->getOneSingleContent($parentData['classid'])));
        }
    }
    
    //根据classid获取栏目的父级顶级栏目id,传入栏目id
    public static function getClassTopId($classid){
        if(!$classid) return 0;
        if($GLOBALS['allclass'][$classid]['uid'] == 0){
            return $GLOBALS['allclass'][$classid]['classid'];
        }else{
            return self::getClassTopId($GLOBALS['allclass'][$classid]['uid']);
        }
    }
}
?>