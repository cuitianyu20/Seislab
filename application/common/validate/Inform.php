<?php

/**
 * share_inform表的验证器
 */
namespace app\common\validate;

use think\Validate;
class Inform extends Validate
{
    protected $rule = [
        'title|标题'=>'require|length:0,40',
        'content|通知内容'=>'require',
        'user_id|作者'=>'require',
        'is_top|是否置顶'=>'require',
    ];
}