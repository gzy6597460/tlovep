<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\25 0025
 * Time: 8:55
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\AlipayClientBase;

/**
 * 退款接口
 * Class Refund
 * @package mikkle\tp_alipay\src
 */
class Refund extends AlipayClientBase
{
    protected  $method = "alipay.trade.refund";
    protected $isDebug =true;
    protected $paramList = ["app_id"];
    protected $bizContentList =[
        "out_trade_no", //订单号
        "refund_amount",//金额
    ];

    public function setRefundBizContentParam($out_trade_no,$refund_amount){

        $this->setBizContentParam([
            "out_trade_no"=>$out_trade_no,
            "refund_amount"=>(string)$refund_amount,
        ]);
        return $this;
    }


}