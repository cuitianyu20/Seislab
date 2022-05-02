<?php
/*
 * 用来进行测试
 */

namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\User;
class Test extends Base
{
    //测试用户的验证器
    public function test1()
    {
        $data = [
            'user_name'=>'Bob',
            'actual_name'=>'Li',
            'occupation'=>'Student',
            'postal_address'=>'吉林省长春市朝阳区',
            'detail_address'=>'清河街道938号',
            'email'=>'12332@163.com',
            'mobile'=>'15784748888',
        ];
        $rule = 'app\common\validate\User';
        return $this->validate($data,$rule);
    }

    public function test2()
    {
        dump(User::get(1));

    }

}