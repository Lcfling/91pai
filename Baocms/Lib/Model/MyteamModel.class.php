<?PHP

class MyteamModel extends CommonModel{
    private $len=0;
    private $pidAry=array();
    private $allnum=0;
    //直推
    public function directpushNum($user_id){
        $sql=D("Users");
        $where['pid']=$user_id;

        $count=$sql->where($where)->count();
       return $count;

    }
//总佣金
    public function countcommission($user_id){
        $sql=D("Fanyong");
        $where['fenyong_id']=$user_id;
        $count=$sql->where($where)->sum('fenyong_edu');
        return $count;

    }


//获取uid的所有下一级
/*public function allteam($curId){

     $users=D('Users');
    $where['pid']=$curId;
    $line_pid=$users->where($where)->select();

    foreach ($line_pid as $val) {
        global $pidAry;
        array_push($this->pidAry,$val['user_id']);
    }
    $this->allteamnum();
}*/
/*public function allteamnum(){
    global $pidAry,$len,$allnum;
    $pidAryLen=count($this->pidAry);
    if($pidAryLen==$len){
        return $allnum;
    }
    $users=D('Users');
    $where['pid']=$this->pidAry[$len];
   // $line_pid=$users->where($where)->select();
    $line_pid=$users->where($where)->find();

    if(is_null($line_pid)){
        //下家没有上级了
        return 0;
    }else{
        $next_userid=$line_pid['user_id'];//当前上级
        $len++;
        $this->allteam($next_userid);
    }

}*/
 /*public function allteamssss($curId){
        global $len;

         $line_pid=$users->where($where)->find();

            if(is_null($line_pid)){
                //下家没有上级了
                return 0;
            }else{
                $next_userid=$line_pid['user_id'];//当前上级
                $len++;
                $this->allteam($next_userid);
            }


    }*/


public function vipteammoneyinfo($curId){

    import('ORG.Util.Page'); // 导入分页类
    $info['fabao_id']=$curId;
    $info['type']='vipbuy';
    $userModel=D('Fanyong');

    $_GET['p']=(int)$_POST['p'];
    $count = $userModel->where($info)->count(); // 查询满足要求的总记录数
    $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数

    $list = $userModel->where($info)->order(array('ID'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
   if($list==null){

       return "[]";
   }
    foreach ($list as $k => $val) {
        $val['avatar']=$this->avatar($val['fenyong_id']);
        $val['nickname']=$this->nickname($val['fenyong_id']);
        $val['fyDate']=date("m/d/i:s",$val['fenyong_id']);
        $list[$k] = $val;
    }
    return $list;


    }
  private function avatar($uid){
      $users=D('Users');
      $map=array();
      $map['user_id']=$uid;
      $line=$users->where($map)->find();
      return $line["face"];
  }
    private function nickname($uid){
        $users=D('Users');
        $map=array();
        $map['user_id']=$uid;
        $line=$users->where($map)->find();
        return $line["nickname"];
    }
  public function directpushinfo($curId){

      import('ORG.Util.Page'); // 导入分页类
      $userModel=D('Users');
      $info['pid']=$curId;
      $_GET['p']=(int)$_POST['p'];
      $count = $userModel->where($info)->count(); // 查询满足要求的总记录数
      $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数

      $list = $userModel->where($info)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
      if($list==null){

          return "[]";
      }
      foreach ($list as $k => $val) {

          $val['reg_time']=date("m/d/i:s",$val['reg_time']);
          $list[$k] = $val;
      }
      return $list;
  }

    public function rebateinfo($curId){

        import('ORG.Util.Page'); // 导入分页类

        $info['fabao_id']=$curId;
        $info['type']  = array('neq','vipbuy');
        $userModel=D('Fanyong');
        $_GET['p']=(int)$_POST['p'];
        $count = $userModel->where($info)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $list = $userModel->where($info)->order(array('ID'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if($list==null){

            return "[]";
        }
        foreach ($list as $k => $val) {
            $val['avatar']=$this->avatar($val['fenyong_id']);
            $val['nickname']=$this->nickname($val['fenyong_id']);
            $val['fyDate']=date("m/d/i:s",$val['fenyong_id']);
            $list[$k] = $val;
        }
        return $list;
    }

}
?>