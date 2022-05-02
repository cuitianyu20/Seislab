<?php

/**
 * 基础控制器
 * 必须继承于think\Controller.php
 */
namespace app\common\controller;
use app\admin\common\model\Site;
use think\Controller;
use think\facade\Session;
use think\facade\Request;

class Base extends Controller
{
    /**
     * 初始化方法
     * 创建常量，公共方法
     * 在所有的方法之前被调用
     */
    protected function initialize()
    {

        //1.检测网站是否关闭
        $this->is_open();

    }

    //检查是否已登录
    public function logined()
    {
        if (Session::has('user_id'))
        {
            $this->error('客官，您已经登录啦~','index/index');
        }
    }

    //检查是否未登录：放在需要登录操作的方法的最前面，例如发布文章
    public function isLogin()
    {
        if (!Session::has('user_id'))
        {
            $this->error('客官，您是不是忘记登录啦~~','user/login');
        }
    }

    //检测站点是否关闭
    public function is_open()
    {
        //1.获取当前站点状态
        $isOpen = Site::where('status',1)->value('is_open');

        //2.判断站点是否关闭，如果已关闭，只允许关闭前台，不允许关闭后台
        if($isOpen ==0 && Request::module() == 'index'){
            //关掉网站
            $info = <<< 'INFO'
<body style="background-color:#333333 ">
<h1 style="color: #eee;text-align: center;margin: 200px">站点维护中...</h1>
</body>
INFO;
            exit($info);

        }

    }

    //检测网站注册是否关闭
    public function is_reg()
    {
        //1.获取当前的注册状态
        $isReg = Site::where('status',1)->value('is_reg');

        //2.如果站点注册功能已关闭，则往首页跳转
        if ($isReg == 0){
            $this->error('注册已关闭!!!','index/index');
        }

    }



}