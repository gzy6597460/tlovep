<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 16:15
 */
namespace app\api\controller;

use mikkle\tp_wechat\WechatApi;
use mikkle\tp_wechat\Wechat;

class Weindex extends WechatApi
{
    public function index()
    {
        $data=Wechat::menu()->getMenu();
        dump($data);
    }

}