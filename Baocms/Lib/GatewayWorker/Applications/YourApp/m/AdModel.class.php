<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   广告模块
 */
defined('LMXCMS') or exit();
class AdModel extends Model{
    public function __construct($field=array('*')){
        $field = $field;
        parent::__construct();
        $this->field = $field;
        $this->tab = array('ad');
    }
    
    //获取数据
    public function getData($limit='',$where=''){
        $param['order'] = 'id desc';
        if($limit) $param['limit'] = $limit;
        if($where) $param['where'] = $where;
        return parent::selectModel($param);
    }
    
    //获取数量
    public function count($where){
        if($where) $param['where'] = $where;
        return parent::countModel($param);
    }
    
    //增加广告
    public function add($data){
        $id = parent::addModel($data);
        $this->idCache($id);
    }
    
    //修改广告
    public function update($data,$id){
        $param['where'] = 'id='.$id;
        parent::updateModel($data,$param);
        $this->idCache($id);
    }
    
    //根据id 增加点击次数
    public function click($id){
        parent::queryModel("update ".DB_PRE."ad set click=click+1 where id=$id");
    }
    
    //删除广告
    public function delete($id){
        $param['where'] = 'id='.$id;
        parent::deleteModel($param);
        //删除广告缓存文件
        file::unLink(ROOT_PATH.'data/ad/'.$id.'.js');
    }
    
    //生成全部广告缓存文件
    public function adCache(){
        $data = $this->getData();
        if($data){
            foreach($data as $v){
                $this->oneCache($v);
            }
        }
    }
    
    //根据id获取一条数据
    public function one($id){
        $param['where'] = 'id='.$id;
        return parent::oneModel($param);
    }
    
    //根据id生成缓存文件
    public function idCache($id){
        $data = $this->one($id);
        $this->oneCache($data);
    }
    
    //根据数据生成单条缓存文件
    private function oneCache($data){
        if(!$data['exstr']) $data['exstr'] = '广告已到期';
        $jsStr = addslashes($this->formatJS($data));
        $str = "if((Date.parse(new Date()) / 1000) > ".$data['extime']."){document.write('".$data['exstr']."')}else{document.write(\"".$jsStr."\");}";
        //保存文件
        file::put(ROOT_PATH.'data/ad/'.$data['id'].'.js',str_replace(array("\n","\r"),'',$str));
    }
    
    //返回并组合js代码字符串
    private function formatJS($data){
        if($data['type'] == 0){
            $width = $data['width'] ? "width='".$data['width']."'" : '';
            $height = $data['height'] ? "height='".$data['height']."'" : '';
            $str = "<a href='".$GLOBALS['public']['weburl']."index.php?m=Ad&a=index&id=".$data['id']."' target='_blank'><img src='".$data['img']."'$width$height /></a>";
        }else if($data['type'] == 1){
            $str = explode("\r\n",$data['string']);
            foreach($str as $v){
                $v = explode('#####',$v);
                $strs .= "<a href='".$GLOBALS['public']['weburl']."index.php?m=Ad&a=index&id=".$data['id']."&url=".$v[1]."' target='_blank'>".$v[0]."</a>";
            }
            $str = $strs;
        }else if($data['type'] == 2){
            $str = string::stripslashes($data['html']);
        }
        return $str;
    }
}
?>