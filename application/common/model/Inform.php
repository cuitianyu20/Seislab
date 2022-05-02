<?php
/*
 * share_inform表的模型
 */

namespace app\common\model;
use think\Model;

class Inform extends Model
{
    //手动指定主键和数据表
    protected $pk = 'id';
    protected $table = 'share_inform';

    protected $autoWriteTimestamp = true;    //开启自动时间戳
    protected $createTime = 'create_time';  //创建时间字段
    protected $updateTime = 'update_time';  //更新时间字段
    protected $dateFormat = 'Y年m月d日';

}