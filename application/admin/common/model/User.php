<?php


namespace app\admin\common\model;

use think\Model;
class User extends Model
{
    protected $pk = 'id';
    protected $table = 'share_user';

    protected $autoWriteTimestamp = true;    //自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = 'Y-m-s h:i:s';
}