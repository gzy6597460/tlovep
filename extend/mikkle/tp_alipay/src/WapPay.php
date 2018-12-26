<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/7/7
 * Time: 21:11
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\AlipayClientBase;
use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class WapPay extends AlipayClientBase
{
    protected  $method = "alipay.trade.wap.pay";
    protected $isDebug =true;
    protected $paramList = ["app_id","return_url","notify_url"];
    protected $bizContentList =[
        "subject", //
        "out_trade_no", //订单号
        "total_amount",
    ];


    protected function buildPublicBizContentParam()
    {
        $publicParam = [
            "product_code"=>"QUICK_WAP_WAY",
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


    public function getH5PayContent(){
        try {
            $this->initParamsHandle();
            if (!$this->requestList){
                throw new Exception("请求的缺少丢失");
            }
            return str_replace( "{{pay_url}}", $this->getQuickPayUrl(), $this->h5PayTpl);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Log::error($e->getMessage());
            if ($this->isDebug){
                Log::notice($e->getMessage());
            }
            return false;
        }
    }


    protected $h5PayTpl = <<<TPL
<form name="punchout_form" method="post" action="{{pay_url}}">
<input type="submit" value="立即支付" >
</form>
<script>document.forms[0].submit();</script>
TPL;

}