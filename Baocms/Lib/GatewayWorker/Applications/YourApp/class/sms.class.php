<?php 
/**
 *  @lcfling
 *
 * 
 *   短信模块
 */
defined('LMXCMS') or exit();
class sms{
    private $c; //topclient 实例
    private $appkey; //采集规则全部数据
    private $secretKey; //列表地址数组
    private $sessionkey; //列表地址数组
    private $sign; //短信签名
    private $temp; //短信签名
    public function __construct(){
        global $config;
        $config['public']=$GLOBALS['public'];
        include $config['plug_dir']."TopSdk.php";
        $this->c = new TopClient;
        $this->c->appkey = $config['public']['sms']['appkey']; // 可替换为您的沙箱环境应用的AppKey
        $this->c->secretKey = $config['public']['sms']['secretKey']; // 可替换为您的沙箱环境应用的AppSecret
        $this->sign= $config['public']['sms']['sign'];
        $this->temp= $config['public']['sms']['temp'];
    }
    public function sendcode($phone,$code){
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($this->sign);
        $req->setSmsParam("{number:'$code'}");
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($this->temp);
        $resp = $this->c->execute($req);
        return $resp;
    }
}
?>