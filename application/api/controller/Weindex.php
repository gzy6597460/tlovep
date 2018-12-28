<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 16:15
 */

namespace app\api\controller;

define("TOKEN", "xlq123123");

use think\Controller;
use wechat\wechatcallbackapi;
use addons\wechat\controller\Welogin;

class Weindex extends Controller
{
    public function index()
    {
        $newmenu = [
            'button' => [
                [
                    'type' => 'view',
                    'name' => "你好",
                    'url' => "https://",
                ],
                [
                    'type' => 'view',
                    'name' => "进入",
                    'url' => "https://",
                ],
                [
                    'name' => "关于我们",
                    'sub_button' => [
                        [
                            'type' => 'view',
                            'name' => "进入",
                            'url' => "https://",
                        ],
                        [
                            'type' => 'view',
                            'name' => "进入",
                            'url' => "https://",
                        ]
                    ]
                ],
            ]
        ];
        $wechatObj = new wechatcallbackapi();
        if (!isset($_GET['echostr'])) {
            $wechatObj->create_menu(json($newmenu));//创建菜单
            $wechatObj->responseMsg();
        } else {
            $wechatObj->valid();
        }
    }

    public function test()
    {
        $login = new Welogin();
        $url = $login->start();
        return $url;
    }

}