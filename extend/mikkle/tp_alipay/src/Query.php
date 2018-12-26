<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\25 0025
 * Time: 8:48
 */

namespace mikkle\tp_alipay\src;

use mikkle\tp_alipay\base\AlipayClientBase;

/**
 * 查询订单
 * Class Query
 * @package mikkle\tp_alipay\src
 */
class Query extends AlipayClientBase
{

    protected  $method = "alipay.trade.query";
    protected $isDebug =true;
    protected $paramList = ["app_id"];
    protected $bizContentList =[
        "out_trade_no", //转账订单号
    ];


    public function setQueryOutTradeNo($out_trade_no){
        $this->setBizContentParam("out_trade_no",$out_trade_no);
        return $this ;
    }

    protected function buildPublicBizContentParam()
    {


    }


}