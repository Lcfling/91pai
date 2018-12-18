<?php 
/**
 *  【梦想cms】 http://www.lmxcms.com
 * 
 *   后台首页控制器
 */
defined('LMXCMS') or exit();
class SitemapAction extends AdminAction{
    private $contentModel = null;
    private $columnModel = null;
    private $tagsModel = null;
    private $ztModel = null;
    private $allcontent = null;
    private $allclass = null;
    private $alltags = null;
    private $allzt = null;
    public function __construct() {
        parent::__construct();
        $this->allclass=$GLOBALS['allclass'];
        if($this->columnModel == null) $this->columnModel = new ColumnModel();
        if($this->tagsModel == null) $this->tagsModel = new TagsModel();
        if($this->ZtModel == null) $this->ZtModel = new ZtModel();
    }
    public function update(){
        $name="sitemap.xml";
        $path=ROOT_PATH.$name;        
        $classdata=$this->Classurllist();
        $ztdata=$this->Zturllist();
        $contentdata=$this->Contenturllist();
        $tagsdata=$this->Tagsurllist();
        $xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml.="<urlset>\n";
        $xml.="<url>\n";
        $xml.="<loc>http://".$_SERVER['SERVER_NAME']."</loc>\n";
        $xml.="<lastmod>".date("Y-m-d",time())."</lastmod>\n";
        $xml.="<changefreq>daily</changefreq>\n";
        $xml.="<priority>1.0</priority>\n";
        $xml.="</url>\n";
        
        foreach($classdata as $v){
            $xml.="<url>\n";
            $xml.="<loc>http://".$_SERVER['SERVER_NAME'].$v."</loc>\n";
            $xml.="<lastmod>".date("Y-m-d",time())."</lastmod>\n";
            $xml.="<changefreq>daily</changefreq>\n";
            $xml.="<priority>0.8</priority>\n";
            $xml.="</url>\n";
        }
        foreach($ztdata as $v){
            $xml.="<url>\n";
            $xml.="<loc>http://".$_SERVER['SERVER_NAME'].$v."</loc>\n";
            $xml.="<lastmod>".date("Y-m-d",time())."</lastmod>\n";
            $xml.="<changefreq>daily</changefreq>\n";
            $xml.="<priority>0.8</priority>\n";
            $xml.="</url>\n";
        }
        foreach($contentdata as $v){
            $xml.="<url>\n";
            $xml.="<loc>http://".$_SERVER['SERVER_NAME'].$v."</loc>\n";
            $xml.="<lastmod>".date("Y-m-d",time())."</lastmod>\n";
            $xml.="<changefreq>daily</changefreq>\n";
            $xml.="<priority>0.6</priority>\n";
            $xml.="</url>\n";
        }
        foreach($tagsdata as $v){
            $xml.="<url>\n";
            $xml.="<loc>http://".$_SERVER['SERVER_NAME'].$v."</loc>\n";
            $xml.="<lastmod>".date("Y-m-d",time())."</lastmod>\n";
            $xml.="<changefreq>daily</changefreq>\n";
            $xml.="<priority>0.4</priority>\n";
            $xml.="</url>\n";
        }
        $xml.="</urlset>";
        file::put($path,$xml);
        rewrite::js_back('站点地图生成成功');
        
        
        
    }
    private function Classurllist(){
        foreach($GLOBALS['allclass'] as $v){
            
            //获取当前栏目链接地址
            $v['classurl'] = classurl($v['classid']);
            $classUrl[] = $v['classurl'];
        }
        return $classUrl;
    }
    private function Contenturllist(){
        $modul;
        foreach($GLOBALS['allclass'] as $v){
            if($v['classtype']=='0'){          
            $this->contentModel=null;
            $this->contentModel = new ContentModel($v['classid']);
            $listInfo = $this->contentModel->getallInfolist($v['classid']);
            if($listInfo){
               foreach($listInfo as $v){
                   $param['type'] = 'content';
                   $param['classid'] = $v['classid'];
                   $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                   $param['time'] = $v['time'];
                   $param['id'] = $v['id'];
                   $v['url'] = $v['url'] ? $v['url'] : url($param);
                   $contenturllist[] = $v['url'];
               }
            }
            }
        }
        return $contenturllist;
    }
    private function Zturllist(){
        $data=$this->ZtModel->getData(NULL);
        foreach($data as $v){
            $zturllist[]=$v['url'];
        }
        return $zturllist;
    }
    private function Tagsurllist(){
        $data=$this->tagsModel->getData(NULL);
        foreach($data as $v){
            $tagsurllist[]=$v['url'];
        }
        return $tagsurllist;
    }
 
}
?>