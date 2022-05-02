<?php


namespace app\admin\controller;

use app\admin\common\model\User as UserModel;
use app\admin\common\controller\Base;
use think\facade\Session;
use think\facade\Request;
class User extends Base
{
    //渲染登录界面
    public function login()
    {
        //1.设置页面标题
        $this->view->assign('title','管理员登录');
        //2.渲染上传数据界面
        return $this->view->fetch('login');
    }

    //验证后台登录
    public function checkLogin()
    {
        //获取表单传递过来的变量
        $data = Request::param();
        $map[] = ['email','=',$data['email']];
        $map[] = ['password','=',sha1($data['password'])];
        $map[] = ['is_admin','=',1];

        $result = UserModel::where($map)->find();
        if ($result){
            Session::set('user_id',$result['id']);
            Session::set('user_name',$result['user_name']);
            Session::set('user_level',$result['is_admin']);
            $this->success('登录成功！','admin/index/index');
        }else{
            $this->error('登录失败,只有网站管理员可以登录！');
        }
    }

    //退出登录
    public function logout()
    {
        //1.清除session
        Session::clear();
        //2.退出登录，跳转到登录页面
        $this->success('退出成功！','admin/user/login');
    }

}