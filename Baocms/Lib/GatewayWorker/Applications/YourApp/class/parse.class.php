<?php
/**
 *  【梦想cms】 http://www.lmxcms.com
 *
 *   解析模板数据并返回
 */
defined('LMXCMS') or exit();

class parse
{
    private $model;
    private $config;
    private $smarty;

    public function __construct(&$smarty)
    {
        global $config;
        $this->config = $config;
        $this->smarty = $smarty;
        //修改smarty配置为前台
        $this->smarty->force_compile = true; //编译文件实时清除
        //
        $this->smarty->template_dir = $this->config['template'] . $GLOBALS['public']['default_temdir'] . '/'; //模板路径
        if (isMobile) {
            $this->smarty->template_dir = $this->config['curr_template'];//强制更换为手机模板
        }
        $this->smarty->compile_dir = ROOT_PATH . 'compile/index/'; //编译文件路径
        $this->smarty->cache_dir = ROOT_PATH . 'compile/cache/index/'; //缓存目录
    }

    //解析首页并返回内容
    public function home()
    {
        $this->smarty->assign('classid', 'home');
        $this->smarty->assign('keywords', $GLOBALS['public']['keywords']);
        $this->smarty->assign('description', $GLOBALS['public']['description']);
        return $this->smarty->fetch('index.html');
    }

    //根据classid解析栏目内容并返回
    public function lists($classid, $model, $count = false)
    {
        $classData = $GLOBALS['allclass'][$classid]; //当前栏目数组
        $parentData = $GLOBALS['allclass'][$classData['uid']];//父栏目数组
        $classData['classurl'] = classurl($classData['classid']);
        if ($parentData) $parentData['classurl'] = classurl($parentData['classid']);
        $this->smarty->assign('classData', $classData);//注入栏目变量数组
        $this->smarty->assign('parentData', $parentData);//注入父栏目数组
        //注入栏目相关变量
        category::assign_class($classData, $parentData, $this->smarty);
        $this->smarty->assign('title', $classData['title']);
        $this->smarty->assign('entitle', $classData['entitle']);
        $this->smarty->assign('keywords', $classData['keywords']);
        $this->smarty->assign('description', $classData['description']);
        $this->smarty->assign('images', $classData['images']);
        $this->smarty->assign('navpos', navpos($classid)); //注入当前位置
        $this->smarty->assign('parentid', $GLOBALS['allclass'][$classid]['uid']);
        $this->smarty->assign('topid', category::getClassTopId($classid));
        if ($classData['classtype'] == 0) { //普通栏目
            //ajax加载
            if ($_GET['ajax'] == 1) {
                $classidArr = category::getClassChild($classid, true);
                foreach ($classidArr as $v) {
                    $classidStr[] = $v['classid'];
                }
                $classidStr = implode(',', $classidStr);
                $count = $count ? $count : $model->q_listCount($classidStr);
                $page = new page($count, $GLOBALS['allclass'][$classid]['pagenum']);
                $data = $model->q_listInfo($page->returnLimit(), $classidStr);
                if ($data) {
                    //赋值url和其他变量
                    foreach ($data as $v) {
                        $param['type'] = 'content';
                        $param['classid'] = $v['classid'];
                        $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                        $param['time'] = $v['time'];
                        $param['id'] = $v['id'];
                        $v['classname'] = $GLOBALS['allclass'][$v['classid']]['classname'];
                        $v['url'] = $v['url'] ? $v['url'] : url($param);
                        $v['classurl'] = classurl($v['classid']);
                        $v['classimage'] = $GLOBALS['allclass'][$v['classid']]['images'];
                        $newData[] = $v;
                    }
                }
                exit(json_encode($data));

            }
            //判断是否为栏目列表
            if ($GLOBALS['allclass'][$classid]['islist'] == 1) {
                $classidArr = category::getClassChild($classid, true);
                foreach ($classidArr as $v) {
                    $classidStr[] = $v['classid'];
                }
                $classidStr = implode(',', $classidStr);
                $count = $count ? $count : $model->q_listCount($classidStr);
                $page = new page($count, $GLOBALS['allclass'][$classid]['pagenum']);
                $data = $model->q_listInfo($page->returnLimit(), $classidStr);
                if ($data) {
                    //赋值url和其他变量
                    foreach ($data as $v) {
                        $param['type'] = 'content';
                        $param['classid'] = $v['classid'];
                        $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                        $param['time'] = $v['time'];
                        $param['id'] = $v['id'];
                        $v['classname'] = $GLOBALS['allclass'][$v['classid']]['classname'];
                        $v['url'] = $v['url'] ? $v['url'] : url($param);
                        $v['classurl'] = classurl($v['classid']);
                        $v['classimage'] = $GLOBALS['allclass'][$v['classid']]['images'];
                        $newData[] = $v;
                    }
                }
                $this->smarty->assign('num', $count);
                $this->smarty->assign('pagenum', $count);
                $this->smarty->assign('page', $page->html()); //注入页码变量
                $this->smarty->assign('list', $newData); //注入信息列表变量
            }
            if ($classid) {
                return $this->smarty->fetch('column/' . $GLOBALS['allclass'][$classid]['listtem'] . '.html');
            } else {
                return $this->smarty->fetch('column/index.html');
            }
        } else if ($classData['classtype'] == 1) { //单页栏目
            $content = string::html_char_dec($model->getOneSingleContent($classid));
            $this->smarty->assign('classcontent', $content);
            return $this->smarty->fetch('single/' . $GLOBALS['allclass'][$classid]['singletem'] . '.html');
        }
    }

    //根据classid解析栏目内容并返回
    public function lists_m($mid, $model, $count = false)
    {
        foreach ($GLOBALS['allclass'] as $v) {
            if ($v['mid'] == $mid) {
                $classidStr[] = $v['classid'];
            }
        }
        //判断是否为栏目列表
        $classidStr = implode(',', $classidStr);

        $count = $count ? $count : $model->q_listCount($classidStr);
        if($classid) {
            $page = new page($count, $GLOBALS['allclass'][$classid]['pagenum']);
        }else{
            $page = new page($count, '12');
        }

        $data = $model->q_listInfo($page->returnLimit(), $classidStr);
        if ($data) {
            //赋值url和其他变量
            foreach ($data as $v) {
                $param['type'] = 'content';
                $param['classid'] = $v['classid'];
                $param['classpath'] = $GLOBALS['allclass'][$v['classid']]['classpath'];
                $param['time'] = $v['time'];
                $param['id'] = $v['id'];
                $v['classname'] = $GLOBALS['allclass'][$v['classid']]['classname'];
                $v['url'] = $v['url'] ? $v['url'] : url($param);
                $v['classurl'] = classurl($v['classid']);
                $v['classimage'] = $GLOBALS['allclass'][$v['classid']]['images'];
                $newData[] = $v;
            }
        }
        $this->smarty->assign('num', $count);
        $this->smarty->assign('pagenum', $count);
        $this->smarty->assign('page', $page->html()); //注入页码变量
        $this->smarty->assign('list', $newData); //注入信息列表变量
        return $this->smarty->fetch('column/index.html');


    }

    //根据信息id和classid解析并返回模板内容
    public function contents($id, $classid, $model)
    {
        //获取上一页和下一页地址
        $prev = $model->prevData($id, 'prev', $classid, array($GLOBALS['allclass'][$classid]['tab']));
        $next = $model->prevData($id, 'next', $classid, array($GLOBALS['allclass'][$classid]['tab']));
        $classData = $GLOBALS['allclass'][$classid]; //当前栏目数组
        $parentData = $GLOBALS['allclass'][$classData['uid']];//父栏目数组
        $classData['classurl'] = classurl($classData['classid']);
        if ($parentData) $parentData['classurl'] = classurl($parentData['classid']);
        $this->smarty->assign('classData', $classData);//注入栏目变量数组
        $this->smarty->assign('parentData', $parentData);//注入父栏目数组
        //注入栏目相关变量
        category::assign_class($classData, $parentData, $this->smarty);
        $this->smarty->assign('navpos', navpos($classid)); //注入当前位置
        $this->smarty->assign('clicknum', "<script type=\"text/javascript\" src=\"" . $GLOBALS['public']['weburl'] . "index.php?m=content&a=clicknnum&classid=" . $classid . "&id=" . $id . "\"></script>");
        $data = $model->updateData($id, array($GLOBALS['allclass'][$classid]['tab']), false);
        foreach ($data as $k => $v) {
            $this->smarty->assign($k, $v);
        }
        $this->smarty->assign('prev', $prev);
        $this->smarty->assign('next', $next);
        $this->smarty->assign('topid', category::getClassTopId($classid));
        return $this->smarty->fetch('content/' . $classData['contem'] . '.html');
    }

    //根据专题id解析专题模板并返回模板内容
    public function zt($id, $ztModel, $ztData = false, $count = false)
    {
        $ztData = $ztData ? $ztData : $ztModel->getOne($id);
        $this->smarty->assign($ztData); //专题数据
        if ($_GET['ajax'] == 1) {
            $count = $count ? $count : $ztModel->infoCount($id);
            $page = new page($count, $ztData['pagenum']);
            $infoData = $ztModel->infoList($id, $page->returnLimit());
            exit(json_encode($data));
        }
        if ($ztData['islist']) { //如果是分页列表加入分页数据
            $count = $count ? $count : $ztModel->infoCount($id);
            $page = new page($count, $ztData['pagenum']);
            $infoData = $ztModel->infoList($id, $page->returnLimit());
            $this->smarty->assign('list', $infoData);
            $this->smarty->assign('num', $count);
            $this->smarty->assign('page', $page->html());
        }
        $navpos = "<a href='" . $GLOBALS['public']['weburl'] . "'>首页</a>" . $GLOBALS['public']['navsplit'] . $ztData['name'];
        $this->smarty->assign('navpos', $navpos);
        return $this->smarty->fetch('zt/' . $ztData['tem'] . '.html');
    }

    //根据Tags数据解析Tags模板并返回模板内容
    public function tags($data, $model, $count = false)
    {
        if ($_GET['ajax'] == 1) {
            $count = $count ? $count : $model->infoCount($data['id']);
            $page = new page($count, $data['pagenum']);
            $infoData = $model->getInfo($data['id'], $page->returnLimit());
            exit(json_encode($data));
        }
        $count = $count ? $count : $model->infoCount($data['id']);
        $page = new page($count, $data['pagenum']);
        $infoData = $model->getInfo($data['id'], $page->returnLimit());
        $this->smarty->assign($data); //Tags数据
        $this->smarty->assign('list', $infoData);
        $this->smarty->assign('num', $count);
        $this->smarty->assign('page', $page->html());
        $navpos = "<a href='" . $GLOBALS['public']['weburl'] . "'>首页</a>" . $GLOBALS['public']['navsplit'] . $data['name'];
        $this->smarty->assign('navpos', $navpos);
        return $this->smarty->fetch('tags/' . $data['tem'] . '.html');
    }

    //修改smarty配置为后台台
    public function __destruct()
    {
        $this->smarty->template_dir = $this->config['curr_template']; //模板路径
        $this->smarty->compile_dir = $this->config['smy_compile_dir'] . RUN_TYPE . '/'; //编译文件路径
        $this->smarty->cache_dir = $this->config['smy_cache_dir'] . RUN_TYPE . '/'; //缓存目录
    }

}

?>