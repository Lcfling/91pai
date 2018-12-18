<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台更新缓存
 */
defined('LMXCMS') or exit();
class CacheAction extends AdminAction{
    private $model = null;
    function __construct(){
        parent::__construct();
    }
    
    public function index(){
        if(isset($_POST['cache'])){
            //更新全站
            if(isset($_POST['all'])){
                $this->all();
            }
            //更新栏目
            if(isset($_POST['allclass'])){
                $this->model = new ColumnModel();
                $this->model->scCacheFile();
            }
            //模型全部缓存
            if(isset($_POST['allmod'])){
                $this->model = new ModuleModel();
                $this->model->scCacheFile();
            }
            //模型表单缓存
            if(isset($_POST['modform'])){
                $this->model = new FieldModel();
                $this->model->cacheInfoform();
            }
            //模型字段缓存
            if(isset($_POST['modfield'])){
                //生成所有字段缓存文件
                $this->model = new FieldModel();
                $this->model->cachefield();
            }
            //更新全部专题信息数量
            if(isset($_POST['ztnum'])){
                $this->model = new ZtModel();
                $data = $this->model->getData(false);
                foreach($data as $v){
                    $this->model->updateNumId($v['id']);
                }
            }
            //广告缓存
            if(isset($_POST['ad'])){
                $this->model = new AdModel();
                $this->model->adCache();
            }
            rewrite::succ('更新成功');
        }
        $this->smarty->display('Cache/index.html');
    }
    
    //更新全站缓存
    private function all(){
        set_time_limit(0); //不限制php执行时间
        $this->smarty->assign('info','生成栏目中，请勿刷新');
        $this->smarty->display('speed.html');
        //更新栏目缓存
        $this->model = new ColumnModel();
        $this->model->scCacheFile();
        rewrite::speed('更新栏目缓存成功');
        //更新模型缓存
        $this->model = new ModuleModel();
        $this->model->scCacheFile();
        rewrite::speed('更新模型缓存成功');
        //更新专题信息数量
        $this->model = new ZtModel();
        $data = $this->model->getData(false);
        foreach($data as $v){
            $this->model->updateNumId($v['id']);
        }
        rewrite::speed('更新全部专题信息数量');
        //更新广告缓存
        $this->model = new AdModel();
        $this->model->adCache();
        rewrite::speed('更新广告缓存成功');
        rewrite::speedSucc('更新完毕');
        rewrite::speedInfoBack('更新全站缓存成功');
        exit;
    }
}
?>