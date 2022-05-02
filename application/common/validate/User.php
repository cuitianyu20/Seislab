<?php
/*
 * share_user表的验证器
 */

namespace app\common\validate;
use think\Validate;

class User extends Validate
{
    //设置share_user表的验证规则
    protected $rule =[
        'user_name|用户名'=>'require|length:1,20|chsAlphaNum',
        'actual_name|真实姓名'=>'require|length:2,20|chsAlphaNum',
        'occupation|职业'=>'require|length:1,100',
        'province|通讯地址-省'=>'require',
        'city|通讯地址-市'=>'require',
        'town|通讯地址-区'=>'require',
        'detail_address|详细地址'=>'require',
        'email|邮箱'=>'require|email|unique:share_user',
        'mobile|手机号'=>'require|mobile|number|unique:share_user',
        'password|密码'=>'require|length:6,20|alphaNum|confirm',
    ];


}