<?php


namespace app\index\controller;

use app\common\controller\Base; //导入公共控制器
use app\common\model\User as UserModel; //导入自定义模型并取别名
use app\common\model\Upload_Data; //导入自定义模型
use app\common\model\Inform; //导入自定义模型
use app\common\model\Earthquake; //导入自定义模型
use app\common\model\Research; //导入自定义模型
use think\facade\Request; //导入请求静态代理
use think\facade\Session;  //导入Session

class Admin extends Base
{

    //管理员查看用户信息列表
    public function userList()
    {
        //1.获取当前用户信息
        $userList = UserModel::order('is_admin','desc')->order('create_time','desc')
            ->paginate(10);

        //2.模板赋值
        $this->view->assign('title','用户管理');
        $this->view->assign('empty','<span style="color: red">没有任何数据</span>');
        $this->view->assign('userList',$userList);

        //3.渲染出用户列表的模板
        return $this->view->fetch('userlist');
    }

    //执行删除用户操作
    public function doDelete()
    {
        //1.获取要删除用户的主键id
        $id = Request::param('id');

        //2.执行删除操作
        if (UserModel::where('id',$id)->delete()){
            return $this->success('删除成功！','userList');
        }else{
            //3.删除失败
            return $this->error('删除失败');
        }
    }

    //管理员查看上传的数据列表
    public function dataList()
    {
        $this->view->assign('title','上传列表'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
    }

    //查询我的上传数据信息
    public function search_list()
    {
        //获取用户id
        $user_id = Request::param('id');
        //根据user_id进行查询我的上传数据
        $data = Upload_Data::where('status',0)->where('demo_data','<>','null')
            ->order('create_time','desc')->select();
        if (!empty($data)){   //如果数据不为空
            //将数据以json数据格式返回到客户端
            echo json_encode($data);
        }else {
            return ['status'=>0,'message'=>"当前无可用数据！"];
        }
    }

    //通过审核
    public function pass()
    {
        if (Request::isAjax()) {
            //获取数据id
            $dataId = Request::param('data_id');
            //执行修改操作
            $res = Upload_Data::where('id',$dataId)->update(['status'=>1]);
            if ($res){
                return ['status'=>0,'message'=>"审核通过！"];
            }else {
                return ['status'=>1,'message'=>"修改失败，请检查！"];
            }
        }
    }

    //管理员发布通知公告
    public function publicInform()
    {
        $this->view->assign('title','发布通知'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板

    }

    //执行发布通知
    public function save()
    {
        if (Request::isPost()){
            //1.获取一下用户提交的信息
            $data = Request::post();
            $res = $this->validate($data,'app\common\validate\Inform');
            if (true !== $res){
                //验证失败
                echo '<script>alert("'.$res.'");history.back(-1)</script>';
            }else {
                //验证成功
                $files = Request::file();
                if (!empty($files['attachment'])){
                    //attachment为数据表的名称、表单的名称，必须一致，返回一个文件对象
                    foreach ($files['attachment'] as $file)
                    {
                        $name = $file->getInfo()["name"];
                        //为文件添加时间子目录
                        $name = date('Ymd').DIRECTORY_SEPARATOR .$name;
                        //文件信息验证成功后，再上传到指定的文件目录，以public为起始目录
                        $info = $file->validate([
                            'size'=>50000000
                        ])->move('attachments',$name);
                        if ($info){
                            $demo[] = $info->getSaveName();    //上传成功则赋值
                        }else {
                            $this->error($file->getError(),'admin/publicInform');        //上传失败则返回错误信息
                        }
                    }
                    //将数组转化为字符串存储到数据库中
                    $data['attachment'] = json_encode($demo);
                }
                //将数据写到数据表中
                if (Inform::create($data)){
                    $this->success('通知发布成功！','index/news');
                }else{
                    $this->error('通知保存失败!');
                }
            }
        }else{
            $this->error('请求类型错误！');
        }

    }

    //显示通知内容
    public function detail()
    {
        //获取传过来的数据
        $informId = Request::param('id');
        $detail = Inform::get(function ($query) use ($informId){
            $query->where('id','=',$informId)
                ->setInc('pv');
        });
        //将存储的字符串转化为数组
        $detail['attachment'] = json_decode($detail['attachment']);
        $attach = $detail['attachment'];

        $this->view->assign('title','通知详情'); //设置页面标题
        $this->view->assign('detail',$detail);
        $this->view->assign('attach',$attach);
        $this->view->assign('empty','<h6 style="margin-left: 10%;color:#23527C;">当前通知无附件</h6>');
        return $this->view->fetch();  //渲染注册模板
    }

    //发布科研动态
    public function publicResearch()
    {
        $this->view->assign('title','发布通知'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
    }

    //执行发布科研动态
    public function saveResearch()
    {
        if (Request::isPost()){
            //1.获取一下用户提交的信息
            $data = Request::post();
            $res = $this->validate($data,'app\common\validate\Research');
            if (true !== $res){
                //验证失败
                echo '<script>alert("'.$res.'");history.back(-1)</script>';
            }else {
                //验证成功
                $file = Request::file();
                if (empty($file['images'])){
                    echo '<script>alert("请上传一张标题图片！");history.back(-1)</script>';
                }else {
                    //images为数据表的名称、表单的名称，必须一致，返回一个文件对象
                    $name = $file['images']->getInfo()['name'];
                    //为文件添加时间子目录
                    $name = date('Ymd').DIRECTORY_SEPARATOR .$name;
                    //文件信息验证成功后，再上传到指定的文件目录，以public为起始目录
                    $info = $file['images']->validate([
                        'size'=>50000000,
                        'ext' =>'jpeg,jpg,png,gif',
                    ])->move('images',$name);
                    if ($info){
                        $demo = $info->getSaveName();    //上传成功则赋值
                    }else {
                        $this->error($file['images']->getError(),'admin/publicResearch');        //上传失败则返回错误信息
                    }

                    $data['images'] = $demo;
                    //将数据写到数据表中
                    if (Research::create($data)){
                        $this->success('动态发布成功！','index/research');
                    }else{
                        $this->error('动态保存失败!');
                    }
                }
            }
        }else{
            $this->error('请求类型错误！');
        }

    }

    //科研动态详情页
    public function researchDetail()
    {
        //获取传过来的数据
        $researchId = Request::param('id');
        $research = Research::get(function ($query) use ($researchId){
            $query->where('id','=',$researchId)
                ->setInc('pv');
        });
        $this->view->assign('title','动态详情'); //设置页面标题
        $this->view->assign('research',$research);
        return $this->view->fetch();  //渲染注册模板

    }

    //更新地震信息
    public function quakeInfo()
    {
        $this->view->assign('title','更新地震目录数据'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
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
                $res = $this->validate($value,'app\common\validate\Quake_Info');
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
                $res = $this->validate($value,'app\common\validate\Quake_Info');
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
    public function quakeUpload()
    {
        //验证客户端传过来的数据是否是ajax形式
        if (Request::isAjax()) {
            //获取最新时间
            $newTime = Earthquake::order('quake_time','desc')->value('quake_time');
            $data = Request::post(); //获取传递过来的数据
            $res = "";
            //循环验证提交文件的数据格式
            foreach ($data as $key=>$value){
                if (strtotime($value['quake_time']) <= strtotime($newTime)){
                    return ['sta'=>-1,'message'=>'请上传发震时间在'.$newTime.'之后的地震信息！'];
                    $res = false;
                }
                if ($value['status'] !== '验证通过!'){
                    return ['sta'=>1,'message'=>'数据列表存在错误，请检查后再上传！'];
                    $res = false;
                }
            }
            //不存在不合格数据
            if ($res !== false){
                foreach ($data as $key=>$value){
                    //将数据写到数据表中
                    if (Earthquake::create($value)){
                        $info = ['sta'=>0,'message'=>'数据上传成功！'];
                    }
                }
                return $info;
            }
        }else {
            $this->error('请求类型错误！');
        }
    }

    //查看地震目录
    public function quakeList()
    {
        $this->view->assign('title','查看地震目录数据'); //设置页面标题
        return $this->view->fetch();  //渲染注册模板
    }

    //检索地震目录数据
    public function searchQuake()
    {
        //根据user_id进行查询我的上传数据
        $data =Earthquake::order('quake_time','desc')->limit(500)->select();
        if (!empty($data)){   //如果数据不为空
            //将数据以json数据格式返回到客户端
            echo json_encode($data);
        }else {
            return ['status'=>0,'message'=>"当前无可用数据！"];
        }
    }

    //地震目录删除操作
    public function delete()
    {
        if (Request::isAjax()) {
            //获取数据id
            $dataId = Request::param('data_id');
            //执行修改操作
            $res = Earthquake::where('id',$dataId)->delete();
            if ($res){
                return ['status'=>0,'message'=>"成功删除！"];
            }else {
                return ['status'=>1,'message'=>"删除失败，请检查！"];
            }
        }
    }











}