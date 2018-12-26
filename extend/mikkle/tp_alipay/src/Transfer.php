<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\19 0019
 * Time: 14:44
 */

namespace mikkle\tp_alipay\src;

use mikkle\tp_alipay\base\AlipayClientBase;

/**
 * 转账到支付宝接口
 * Class Transfer
 * @package mikkle\tp_alipay\src
 */
class Transfer extends AlipayClientBase
{
    protected  $method = "alipay.fund.trans.toaccount.transfer";
    protected $isDebug =true;
    protected $paramList = ["app_id"];
    protected $bizContentList =[
        "payee_account", //转账账号
        "out_biz_no", //转账订单号
        "amount",//转账金额
    ];


    public function setTransferBizContentParam($payee_account,$amount,$out_biz_no=false){
        if (empty( $out_biz_no)){
            $out_biz_no = time();
        }
        $this->setBizContentParam([
            "payee_account"=>$payee_account,
            "amount"=>(string)$amount,
            "out_biz_no"=>$out_biz_no,
        ]);
        return $this;
    }

    protected function buildPublicBizContentParam()
    {
        $publicParam = [
            "payee_type"=>"ALIPAY_LOGONID",
        ];
        $this->bizContent = array_merge($this->bizContent ,$publicParam ) ;
    }

}