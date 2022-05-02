<?php


namespace app\index\controller;

use app\common\controller\Base; //导入公共控制器
use app\common\model\User as UserModel; //导入自定义模型并取别名
use app\common\model\Upload_Data; //导入自定义模型并取别名
use think\facade\Request; //导入请求静态代理
use think\facade\Session;  //导入Session
use phpmailer\phpmailer;
class User extends Base
{
    //注册页面
    public function register()
    {
        //检测注册功能是否开启
        $this->is_reg();

        $this->assign('title','用户注册'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
    }

    //处理用户提交的注册信息,并写到share_user表中
    public function  insert()
    {	//前端提交的必须是Ajax请求再进行验证与新增操作
        if(Request::isAjax()){
            //1.数据验证
            $data = Request::post();  //要验证的数据
            $rule = 'app\common\validate\User';  //自定义的验证器

            //开始验证: $res 中保存错误信息,成功返回true
            $res = $this->validate($data,$rule);
            if (true !== $res){  //验证失败
                return ['status'=> -1, 'message'=>$res];
            }else { //验证成功
                //2. 将数据写入到数据表zh_user中,并对写入结果进行判断
                if($user = UserModel::create($data)){
                    //注册成功之后实现自动登录
                    $res1 = UserModel::get($user->id);
                    Session::set('user_id',$res1->id);
                    Session::set('user_name',$res1->user_name);
                    return ['status'=>1, 'message'=>'恭喜,注册成功~~'];
                } else {
                    return ['status'=>0, 'message'=>'注册失败~~'];
                }
            }
        }else{
            $this->error('请求类型错误','register');
        }
    }

    //用户登录
    public function login()
    {
        $this->logined();
        return $this->view->fetch('login',['title'=>'用户登录']);

    }

    //用户登录验证
    public function loginCheck()
    {
        if(Request::isAjax()){
            //1.数据验证
            $data = Request::post();  //要验证的数据
            $rule = [
                'email|邮箱'=>'require|email',
                'password|密码'=>'require|alphaNum'
            ];  //自定义的验证规则

            //开始验证: $res 中保存错误信息,成功返回true
            $res = $this->validate($data,$rule);
            if (true !== $res){  //验证失败
                return ['status'=> -1, 'message'=>$res];
            }else { //验证成功
                //进行验证，如果数据库中的数据与用户提交的数据不符，然后返回null
                $result = UserModel::get(function ($query) use ($data){
                    $query->where('email',$data['email'])
                        ->where('password',sha1($data['password']));
                });
                if (null == $result){
                    //验证不成功，返回错误信息
                    return ['status'=>0, 'message'=>'邮箱或密码不正确，请检查'];
                }else{
                    //将用户数据写到Session中
                    Session::set('user_id',$result->id);
                    Session::set('user_name',$result->user_name);
                    Session::set('user_level',$result->is_admin);
                    return ['status'=>1, 'message'=>'恭喜,登录成功~~'];
                }

            }
        }else{
            $this->error('请求类型错误','login');
        }
    }

    //用户退出登录
    public function logout()
    {
        Session::clear();
        $this->success('成功退出登录','index/index');
    }

    //用户列表
    public function userInfo()
    {

        //检查用户是否登录
        $this->isLogin();

        //1.获取当前用户id和用户的级别is_admin
        $data = [];
        $data['user_id'] = Session::get('user_id');
        $data['user_level'] = Session::get('user_level');

        //2.获取当前用户信息
        $userInfo = UserModel::where('id',$data['user_id'])->find();

        //3.模板赋值
        $this->view->assign('title','个人资料');
        $this->view->assign('empty','<span style="color: red">没有任何数据</span>');
        $this->view->assign('userInfo',$userInfo);

        //4.渲染出用户列表的模板
        return $this->view->fetch('userinfo');
    }

    //渲染编辑用户的界面
    public function userEdit()
    {
        //1.获取要编辑用户的主键
        $userId = Request::param('id');

        //2.根据主键查询
        $userEdit = UserModel::where('id',$userId)->find();

        //3.设置编辑界面的模板变量
        $this->view->assign('title','编辑用户');
        $this->view->assign('userEdit',$userEdit);

        //4.渲染出编辑界面
        return $this->view->fetch('useredit');
    }

    //执行用户信息的保存工作
    public function doEdit()
    {
        //1.获取用户提交的信息
        $data = Request::param();

        //2.取出主键
        $id = $data['id'];

        //3.删除主键id
        unset($data['id']);

        //4.执行更新操作
        if(UserModel::where('id',$id)->data($data)->update()){
            //更新Session内容
            $result = UserModel::get(function ($query) use ($id){
                $query->where('id',$id);
            });
            Session::set('user_name',$result->user_name);
            return $this->success('更新成功！','userInfo');
        }else{
            return $this->error('没有更新或更新失败');
        }
    }

    //修改用户密码
    public function alter()
    {
        $this->view->assign('title','修改密码'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
    }

    //发送验证码
    public function sendEmail(){
        header("content-type:text/html;charset=utf-8");
        //获取前端传递过来的邮箱值
        $email = Request::param('email');
        $id = Request::param('user_id');
        //设置查询条件
        $map = [];
        $map[] = ['email','=',$email];
        //查询邮箱是否注册
        $result = UserModel::get(function ($query) use ($map){
            $query->where($map);
        });
        if (null == $result){
            //验证不成功，返回错误信息
            return ['status'=>1, 'message'=>'该邮箱未注册！'];
        }
        //如果已登陆，查询邮箱是否是本人邮箱
        if ($id != ""){
            $map[] = ['id','=',$id];
        }
        $result2 = UserModel::get(function ($query) use ($map){
            $query->where($map);
        });
        if (null == $result2){
            //验证不成功，返回错误信息
            return ['status'=>2, 'message'=>'请输入本人注册邮箱！'];
        }

        //获取用户名
        $user_name = $result['user_name'];
        //获取当前时间
        $time = date('Y-m-d H:i:s');
        //生成随机6位大小写字母加数字的字符串验证码
        $string = $this->getRandomString();

        $mail = new phpmailer(); //实例化
        // 使用SMTP方式发送
        $mail->isSMTP();
        // 设置邮件的字符编码
        $mail->CharSet='UTF-8';
        // 企业邮局域名
        $mail->Host = 'smtp.163.com';
        //设置smtp服务器的远程服务器端口号 默认25
        $mail->Port = 25;
        // 启用SMTP验证功能
        $mail->SMTPAuth = true;
        //邮件发送人的用户名(请填写完整的email地址)
        $mail->Username = 'tycuicn@163.com' ;
        // 邮件发送人的 密码 （授权码）
        $mail->Password = 'TMZDVHCYBZROIEWA';  //授权码
        //邮件发送者email地址
        $mail->From ="tycuicn@163.com";
        //发送邮件人的标题
        $mail->FromName ="地震台阵探测实验室数据管理中心管理员";
        //收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        $mail->AddAddress($email,$user_name);
        //是否使用HTML格式
        $mail->isHTML(true);
        //邮件标题
        $mail->Subject = '修改用户密码';
        //邮件内容
        $mail->Body =  "亲爱的".$user_name."：<br/>您在".$time."提交了修改密码的请求。系统给您发送的验证码是&nbsp;
        <span style=\"background: #ccc;width:60px;color: #fff;\">$string</span>&nbsp;，该验证码有效时间为3分钟，请注意自己的帐号安全，
        不要外泄密码！！<br/>此邮件由系统自动发出,请勿直接回复。";

        //发送邮件
        $res = $mail->send();
        halt($res);
        //检查是否发送成功
        if ($res){
            return ['status'=>0,'message'=>"验证码已发送，请尽快查收！","code"=>$string,"time"=>$time];
        }else {
            $str  =  "邮件发送失败. <p>";
            $str .= "错误原因: " . $mail->ErrorInfo;
            return ['status'=>-1,'message'=>$str];
        }
    }

    //生成随机字母加数字 字符串
    public function getRandomString($len = 6, $chars = null)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000 * (double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    //执行密码修改操作
    public function pdSave(){
        //1.获取用户修改后的信息
        $new_password = Request::param('new_pd');
        $email = Request::param('email');

        //2.执行修改
        $result = UserModel::where('email',$email)->update(['password'=>sha1($new_password)]);

        if ($result){
            return ['status'=>0,'message'=>"修改成功！"];
        }else {
            return ['status'=>1,'message'=>"不能与原密码相同，请检查！"];
        }
    }

    //渲染我的上传数据信息页面
    public function myUpload()
    {
        $this->view->assign('title','我的上传'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
    }

    //查询我的上传数据信息
    public function search_upload()
    {
        //获取用户id
        $user_id = Request::param('id');
        //根据user_id进行查询我的上传数据
        $data = Upload_Data::where('user_id',$user_id)->order('create_time','desc')
            ->select();
        if (!empty($data)){   //如果数据不为空
            //将数据以json数据格式返回到客户端
            echo json_encode($data);
        }else {
            return ['status'=>0,'message'=>"当前无可用数据！"];
        }
    }



}