<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//过滤一下通知标题长度
if (!function_exists('getDetContent'))
{
    function getDetContent($content)
    {
        if (strlen($content)>35){
            return  mb_substr($content,0,35)."...";
        }else {
            return $content;
        }
    }
}

//过滤一下附件名称
if (!function_exists('getAttachContent'))
{
    function getAttachContent($vo)
    {
        return  mb_substr($vo,9);
    }
}

//添加下载路径
if (!function_exists('getAttachRoute'))
{
    function getAttachRoute($vo)
    {
        return  '/project/public/attachments/'.$vo;
    }
}

//过滤图片
if (!function_exists('getTextContent'))
{
    function getTextContent($content)
    {
        $replace =  preg_replace("/<img.*?>/si","",strip_tags($content));
        if (strlen($replace)>40){
            return  mb_substr($replace,0,40)."...";
        }else {
            return $replace;
        }

    }
}

