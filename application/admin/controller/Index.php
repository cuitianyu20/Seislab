<?php


namespace app\admin\controller;

use app\admin\common\model\Site as SiteModel;
use app\admin\common\controller\Base;
use think\facade\Session;
use think\facade\Request;

class Index extends Base
{
    public function index()
    {
        //检查用户是否登录
        $this->isLogin();

        //获取站点信息
        $siteInfo = SiteModel::get(['status'=>1]);

        //对模板赋值
        $this->view->assign('siteInfo',$siteInfo);
        $this->view->assign('title','网站管理');
        //渲染后台首页
        return $this->view->fetch('index');
    }


    //保存站点的修改信息
    public function save()
    {
        //1.获取到数据
        $data = Request::param();

        //2.执行更新操作
        if(SiteModel::update($data)){
            $this->success('更新成功！','index');
        }
        $this->error('更新失败！','index');


    }


}