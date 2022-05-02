<?php
namespace app\index\controller;

use app\common\controller\Base;     //导入公共控制器
use app\common\model\Upload_Data;  //导入自定义模型
use app\common\model\Inform;   //导入自定义模型
use app\common\model\Research; //导入自定义模型
use app\common\model\Catalog;  //导入自定义模型
use http\Header;
use think\facade\Request;
use think\facade\Session;  //导入Session

class Index extends Base
{
    //首页
    public function index()
    {
        //查询发布的最新通知信息
        $informList = Inform::order('create_time','desc')->limit(6)->select();
        //查询发布的最新科研动态
        $researchList = Research::order('create_time','desc')->limit(3)->select();
        $this->view->assign('informList',$informList);
        $this->view->assign('researchList',$researchList);
        return $this->view->fetch();
    }

    //搜索功能
    public function seek()
    {

        //全局查询条件
        $map = [];    //将所有的查询条件都封装在这个数组中
        //条件1
        $map[] = ['status','=',1];      // 这里的等号不能省略

        //实现搜索功能
        $keywords = Request::param('keywords');
        if (!empty($keywords)){
            //条件2
            $map[] = ['title|content','like','%'.$keywords.'%'];        //根据通知标题和内容模糊查询
        }
        $resList = Inform::where($map)->order('create_time','desc')->paginate(10,false,['query'=>request()->param()]);

        $this->view->assign('title','查询结果');
        $this->view->assign('empty','<h5 style="color: #23527C;text-align: center;">对不起，没有找到合适的结果！</h5>');
        $this->view->assign('resList',$resList);
        return $this->view->fetch();
    }

    //单条上传数据界面
    public function insert()
    {
        //1.用户登录才可以上传数据
        $this->isLogin();
        //2.设置页面标题
        $this->view->assign('title','提交数据');
        //3.渲染上传数据界面
        return $this->view->fetch('insert');
    }

    //保存单条上传数据
    public function save()
    {
        //判断提交的类型
       if (Request::isPost()){

           //判断数据是否上传
           if (empty($_FILES['demo_data']['name'])){
               $this->error('请选择上传文件');
           }
           //获取用户提交的信息
           $data = Request::post();
           $res = $this->validate($data,'app\common\validate\Upload_Data');
           if ($res !== true){
               //验证失败
               echo '<script>alert("'.$res.'");history.back(-1)</script>';
           }else {
               //验证通过
               //获取上传的数据信息
               $file = Request::file('demo_data');
               //文件信息验证，验证成功后上传服务器指定目录,以public为起始目录
               $info = $file->validate([
                   'size'=>7000000,
                   'ext'=>'mseed',
               ])->move('uploads/');

                if ($info){     //查看文件是否上传到服务器成功
                    $data['demo_data'] = $info->getSaveName();   //上传成功则赋值
                }else {
                    //上传失败则返回错误信息
                    $this->error($file->getError(),'user/myUpload');
                }
               //将数据写到数据表中
                if (Upload_Data::create($data)){
                    $this->success('数据上传成功！','user/myUpload');
                }else {
                    $this->error('数据上传失败，请检查！');
                }
           }
       }else {
           $this->error('请求类型错误！');
       }

    }

    //多条数据上传文件
    public function uploadData()
    {
        //判断提交的类型
        if (Request::isAjax()){
            //获取上传的数据信息
            $id = Request::param('id');
            $file = Request::file('file');
            //文件信息验证，验证成功后上传服务器指定目录,以public为起始目录
            $info = $file->validate([
                'size'=>7000000,
                'ext'=>'mseed',
            ])->move('uploads/');
            if (!$info){      //查看文件是否上传到服务器成功
                return ['status'=>-1,"message"=>$file->getError()]; //上传失败则返回错误信息
            }else {
                $data['demo_data'] = $info->getSaveName();   //上传成功则获取存储在服务器的名字
            }
            //将数据写到数据表中
            $res = Upload_Data::where('id',$id)->data($data)->update();
            if ($res){
                return ['status'=>1,"message"=>"数据上传成功！"];
            }else {
                return ['status'=>0,"message"=>"数据上传失败，请检查！"];
            }
        }else {
            $this->error('请求类型错误！');
        }

    }

    //多条上传
    public function uploads()
    {
        //1.用户登录才可以上传数据
        $this->isLogin();
        //2.设置页面标题
        $this->view->assign('title','提交数据');
        //3.渲染上传数据界面
        return $this->view->fetch('uploads');

    }

    //文件数据验证
    public function verify()
    {
        //定义编码格式
        header("Content-type: text/html; charset=utf-8");
        //验证客户端传过来的数据是否是ajax形式
        if (Request::isAjax()) {
            $data = Request::post(); //获取传递过来的数据
            //循环验证提交文件的数据格式
            foreach ($data as $key => &$value){
                $res = $this->validate($value,'app\common\validate\Uploads');
                $key = $key +1;
                if ($res !== true){
                    $value = ['status'=>"$res"]+$value;
                    $value = ['序号'=>"$key"]+$value;
                }else {
                    $value = ['status'=>'验证通过!']+$value;
                    $value = ['序号'=>"$key"]+$value;
                }
            }
            //将数据以json格式返回给前端
            echo json_encode($data);
        }else {
            $this->error('请求类型错误！');
        }
    }

    //文件修改后再次验证
    public function editVerify()
    {
        //定义编码格式
        header("Content-type: text/html; charset=utf-8");
        //验证客户端传过来的数据是否是ajax形式
        if (Request::isAjax()) {
            $data = Request::post(); //获取传递过来的数据
            //循环验证提交文件的数据格式
            foreach ($data as $key => &$value){
                $res = $this->validate($value,'app\common\validate\Uploads');
                if ($res !== true){
                    $value['status'] = "$res";
                }else {
                    $value['status'] = "验证通过!";
                }
            }
            //将数据以json格式返回给前端
            echo json_encode($data);
        }else {
            $this->error('请求类型错误！');
        }
    }

    //上传多条信息
    public function submit()
    {
        //定义编码格式
        header("Content-type: text/html; charset=utf-8");
        //验证客户端传过来的数据是否是ajax形式
        if (Request::isAjax()) {
            $data = Request::post(); //获取传递过来的数据
            $user_id = Session::get('user_id');
            $res = "";
            //循环验证提交文件的数据格式
            foreach ($data as $key=>$value){
                if ($value['status'] !== '验证通过!'){
                    return ['sta'=>1,'message'=>'数据列表存在错误，请检查后再上传！'];
                    $res = false;
                }
            }
            $info = "";
            //不存在不合格数据
            if ($res !== false){
                foreach ($data as $key=>$value){
                    $value['user_id'] = $user_id;
                    //将数据写到数据表中
                    if (Upload_Data::create($value)){
                        $info = ['sta'=>0,'message'=>'数据上传成功！'];
                    }
                }
                return $info;
            }
        }else {
            $this->error('请求类型错误！');
        }
    }

    //下载数据界面
    public function download()
    {
        //1.用户登录才可以上传数据
        $this->isLogin();
        //2.设置页面标题
        $this->view->assign('title','下载数据');
        //3.渲染上传数据界面
        return $this->view->fetch('download');
    }

    //下载数据查询
    public function search()
    {
        //定义编码格式
        header("Content-type: text/html; charset=utf-8");
        //验证客户端传过来的数据是否是ajax形式
        if (Request::isAjax()) {
            $require_data = Request::param(); //获取传递过来的数据
            $map = array();     //定义查询条件数组
            //设置查询条件
            if (!empty($require_data['network'])) {
                //查询与服务器中network相等的数据
                $map[] = ['network', '=', $require_data['network']];
                if (!empty($require_data['station'])) {
                    //查询与服务器中station相等的数据
                    $map[] = ['station', '=', $require_data['station']];
                    if (!empty($require_data['channel'])) {
                        //根据channel进行模糊查询
                        $map[] = ['channel', 'like', $require_data['channel'] . '%'];
                    }
                }
            }else {
                if (!empty($require_data['station']) or !empty($require_data['channel'])){
                    //如果没有输入台网信息，直接根据台站和通道查询将返回错误信息
                    return ['status'=>1, 'message'=>'请输入台网信息或台站信息再进行查询！'];
                }
            }

            //经纬度有效区间判断
            if ((empty($require_data['longitude_max']) and !empty($require_data['longitude_min'])) or
                (!empty($require_data['longitude_max']) and empty($require_data['longitude_min'])) or
                (!empty($require_data['latitude_max']) and empty($require_data['latitude_min'])) or
                (empty($require_data['latitude_max']) and !empty($require_data['latitude_min'])))
            {
                return ['status'=>-3, 'message'=>'请输入有效区间范围！'];
            }

            //经纬度最大值与最小值判断
            if (!empty($require_data['longitude_max']) and !empty($require_data['longitude_min'])) {
                if ($require_data['longitude_max'] > $require_data['longitude_min']){
                    //根据经度区间进行查找
                    $map[] = ['longitude', 'between', [$require_data['longitude_min'], $require_data['longitude_max']]];
                }else {
                    return ['status'=>-1, 'message'=>'最大经度值必须大于最小经度值！'];
                }
            }
            if (!empty($require_data['latitude_max']) and !empty($require_data['latitude_min']))
            {
                if ($require_data['latitude_max'] > $require_data['latitude_min']){
                    //根据经度区间进行查找
                    $map[] = ['latitude', 'between', [$require_data['latitude_min'], $require_data['latitude_max']]];
                }else {
                    return ['status'=>-2, 'message'=>'最大纬度值必须大于最小纬度值！'];
                }
            }

            //开始记录时间与停止记录时间判断
            if (!empty($require_data['starttime']))
            {
                //根据开始记录时间进行查询
                $map[] = ['starttime', '> time', $require_data['starttime']];
            }
            if (!empty($require_data['endtime'])){
                //根据结束记录时间进行查询
                $map[] = ['endtime', '< time', $require_data['endtime']];
            }
           if (empty($map)){
               return ['status'=>2, 'message'=>'请输入查询条件！'];
           }else {
               $map[] = ['status', '=', 1];
               //按照查询条件进行查询数据
               $data = Upload_Data::where($map)->order('create_time','desc')
                   ->select();
               if (!empty($data)){   //如果数据不为空
                   //将数据以json数据格式返回到客户端
                   echo json_encode($data);
               }
           }
        }else {
            $this->error('请求类型错误！');
        }
    }

    //传递数据下载数量
    public function downtime()
    {
        if (Request::isAjax()) {
            $dataId = Request::param('data_id');
            $res = Upload_Data::get(function ($query) use ($dataId) {
                $query->where('id', '=', $dataId)
                    ->setInc('downtimes');
            });
        }
    }

    //中心简介界面
    public function info(){
        //1.设置页面标题
        $this->view->assign('title','数据中心');
        //2.渲染上传数据界面
        return $this->view->fetch('info');
    }

    //仪器设备界面
    public function device(){
        //1.设置页面标题
        $this->view->assign('title','仪器设备');
        //2.渲染上传数据界面
        return $this->view->fetch('device');
    }

    //地震界面
    public function earthquake(){
        //1.设置页面标题
        $this->view->assign('title','最近地震');
        //2.渲染上传数据界面
        return $this->view->fetch('earthquake');
    }

    //地震目录数据查询
    public function catalog(){
        //定义编码格式
        header("Content-type: text/html; charset=utf-8");
        //判断请求是否是ajax请求
        if (Request::isAjax()){
            $map = array();     //定义查询条件数组
            $res = Catalog::where($map)->order('quake_time','desc')
            ->select();     //根据查询条件进行查询并且返回数据按照发震时间排序
            if (!empty($res)){
                //将数据以json数据格式返回到客户端
                echo json_encode($res);
            }
        }else {
            $this->error('请求类型错误！');
        }
    }

    //渲染新闻动态界面
    public function news(){

        //查询发布的通知信息
        $informList = Inform::order('is_top','desc')->order('create_time','desc')
            ->paginate(15);
        //设置页面标题
        $this->view->assign('title','通知公告');
        $this->view->assign('empty','<h5 style="color: #23527C;text-align: center;">当前还没有发布任何通知！！！</h5>');
        $this->view->assign('informList',$informList);
        //渲染上传数据界面
        return $this->view->fetch('news');
    }

    //显示科研动态内容列表
    public function research()
    {
        //查询发布的动态信息
        $researchList = Research::order('is_top','desc')->order('create_time','desc')
                ->paginate(10);
        //设置页面标题
        $this->view->assign('title','科研动态');
        $this->view->assign('researchList',$researchList);
        $this->view->assign('empty','<h4>当前还没有发布任何动态！！！</h4>');
        //渲染上传数据界面
        return $this->view->fetch('research');

    }



}
