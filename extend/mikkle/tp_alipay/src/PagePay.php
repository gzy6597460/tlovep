<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\9 0009
 * Time: 9:38
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\AlipayClientBase;
use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class PagePay extends AlipayClientBase
{
    protected  $method = "alipay.trade.page.pay";
    protected $isDebug =false;
    protected $paramList = ["app_id","notify_url"];
    protected $bizContentList =[
        "subject", //
        "out_trade_no", //订单号
        "total_amount",
    ];


    protected function buildPublicBizContentParam()
    {
        $publicParam = [
            "product_code"=>"FAST_INSTANT_TRADE_PAY",
            //  "seller_id"=>"",
        ];
        $this->bizContent = array_merge($this->bizContent ,$publicParam ) ;
    }


    public function getQuickPayUrl()
    {
        try {
            $this->initParamsHandle();
            if (!$this->requestList){
                throw new Exception("请求的缺少丢失");
            }
            return $this->gatewayUrl.Tools::formatBizQueryParaMap($this->requestList,true,[]);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Log::error($e->getMessage());
            if ($this->isDebug){
                Log::notice($e->getMessage());
            }
            return false;
        }
    }

    public function getPostParam(){
        try {
            $this->initParamsHandle();
            if (!$this->requestList){
                throw new Exception("请求的缺少丢失");
            }
            return $this->requestList;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Log::error($e->getMessage());
            if ($this->isDebug){
                Log::notice($e->getMessage());
            }
            return false;
        }
    }





}