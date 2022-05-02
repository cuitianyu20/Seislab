<?php


namespace app\common\validate;
use think\Validate;

class Quake_Info extends Validate
{
    //设置share_earthquake_catalog表的验证规则
    protected $rule =[
        'quake_time|发震时间'=>'require|date',
        'longitude|经度' => 'require',
        'latitude|纬度' => 'require',
         'depth|深度'   => 'require|integer',
        'magnitude|震级'=> 'require|float',
        'location|位置' => 'require',
    ];

}