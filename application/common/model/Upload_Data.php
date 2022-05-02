<?php


namespace app\common\model;

use think\Model;

class Upload_Data extends Model
{
    //手动指定主键和数据表
    protected $pk = 'id';
    protected $table = 'share_upload_data';

    protected $autoWriteTimestamp = true;    //开启自动时间戳
    protected $createTime = 'create_time';  //创建时间字段
    protected $updateTime = 'update_time';  //更新时间字段
    protected $dateFormat = 'Y年m月d日';

    //自动完成设置
    protected $auto = [];   //无论是新增或是更新都要设置的字段
    //仅新增的时候有效
    protected $insert = ['create_time','status'=>0];
    //仅更新的时候有效
    protected $update = ['update_time'];



}