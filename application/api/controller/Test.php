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
/**
 * 测试接口
 */
class Test extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $curl = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    public function __construct(Curl $curl)
    {
        parent::__construct();
        $this->curl = $curl;
    }

    public function test(){
        $res = Config::get("wechat.default_options_name");
        $this->success('nihao',$res);
    }
}