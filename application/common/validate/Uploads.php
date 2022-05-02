<?php


namespace app\common\validate;
use think\Validate;

class Uploads extends Validate
{
    //设置share_upload_data表的验证规则
    protected $rule =[
        'network|台网'=>'require',
        'station|台站'=>'require',
        'longitude|台站经度'=>'require',
        'latitude|台站纬度'=>'require',
        'elevation|台站海拔'=>'require',
        'starttime|台站开始记录时间'=>'require|date',
        'endtime|台站开始记录时间'=>'require|date',
        'das|分布式光纤声传感器(DAS)'=>'require',
        'das_type|分布式光纤声传感器(DAS)类型'=>'require',
        'sensor|传感器'=>'require',
        'sensor_type|传感器类型'=>'require',
        'sensor_depth|传感器深度'=>'require|integer',
        'sample_rate|采样速率'=>'require|integer',
        'gain|增益'=>'require|integer',
        'data_stream_recorded|记录的数据流'=>'require|integer',
        'channel_code|数据流中的记录通道'=>'require',
        'channel|传感器通道名称'=>'require',
    ];

}