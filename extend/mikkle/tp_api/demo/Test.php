<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\6\15 0015
 * Time: 16:29
 */




use app\base\service\PayCenter;
use think\Controller;

class Test extends Controller
{
    function index(){
        $api = new PayCenter();
        $params=[
            "platform"=>2,
            "source"=>1,
            "userData"=>"".time(),
            "psub"=>1,
            "uid"=>1,
            "version"=>"jldt_1_1",
            "goodsId"=>999,
            "channelId"=>10000,
            "payment"=>114,
            "gameId"=>4000,
            "price"=>1
        ];
        $api->setUrl("https://test-pay..com/pay/v1.alipay/index.html")
            ->setParam($params);
        dump($api->getResult());
        dump($api);
    }

}