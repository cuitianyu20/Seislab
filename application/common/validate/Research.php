<?php

/**
 * share_research表的验证器
 */
namespace app\common\validate;

use think\Validate;
class Research extends Validate
{
    protected $rule = [
        'title|标题'=>'require|length:0,40',
        'content|科研动态内容'=>'require',
        'user_id|作者'=>'require',
        'is_top|是否置顶'=>'require',
    ];
}