<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Request;
use think\Validate;
use mikkle\tp_tools\Curl;
use app\common\library\Token;
/**
 * 测试接口
 */
class Test extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    protected $curl = null;

    public function _initialize()
    {
        parent::_initialize();
    }

//    public function __construct(Curl $curl)
//    {
//        parent::__construct();
//        $this->curl = $curl;
//    }

    public function test(){
//        $res = Token::has('cbb515f8-d53a-47b4-b45b-cd0caba71ee3',1);
//        dump($res);
        //1
//        $curl = new Curl();
//        $res =$curl->get('www.baidu.com');
//        //2
//        $res =Curl::get('www.baidu.com');
        //3
//        $res = $curl->get('www.baidu.com');
        $this->success('nihao',1);
    }
}