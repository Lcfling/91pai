<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   模块基类
 */
defined('LMXCMS') or exit();
class Model extends db{
    protected $db=null;
    protected $field = array('*');
    protected $tab = array();
    protected $join = array();
    protected function __construct(){
        $this->db=parent::getConn();
    }

    //更新数据
    protected function updateModel($data,$param){
        return parent::updateDB($this->tab[0],$data,$param);
    }
    //增加数据并返回id
    protected function addModel($data){
       return parent::addDB($this->tab[0],$data);
    }
    //获取一条数据
    protected function oneModel($param){

		return parent::oneDB($this->tab['0'],$this->field,$param);
    }
    //获取数据
    protected function selectModel($param=array()){
       if($param['field']){
           $this->field=$param['field'];
       }
       return parent::selectDB($this->tab['0'],$this->field,$param);
    }

    //返回记录数
    protected function countModel($param=array()){
        return parent::countDB($this->tab['0'],$param);
    }

    //删除
    protected function deleteModel($param){
        return parent::deleteDB($this->tab['0'],$param);
    }

    //join
    protected function joinModel($param=array()){
        if(!$this->join || !$this->join['field'] || !$this->join['on']){
            exit('join参数设置有误');
        }
        return parent::joinDB($this->join,$param);
    }

    //创建数据表
    protected function createModel($tabname,array $fileArr,$charset='utf8',$engine='MyISAM'){
        return parent::createDB($tabname,$fileArr,$charset,$engine);
    }

    //增加字段
    protected function fieldModel($tabname,$type,$fieldname,$default=''){
        return parent::fieldDB($tabname,$type,$fieldname,$default);
    }
    //删除字段
    protected function delFieldModel($tab,$fieldName){
        return parent::delFieldDB($tab,$fieldName);
    }

    //删除数据表
    protected function delTabModel($tabname){
        return parent::delTabDB($tabname);
    }

    //执行查询原生sql并返回数据
    protected function sqlModel($sql){
        return parent::sqlDb($sql);
    }
    //执行原生sql语句
    protected function queryModel($sql){
        return parent::queryDb($sql);
    }

}
?>