<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/5/21
 * Time: 16:10
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

/**
 * title 付款到银行
 * User: Mikkle
 * QQ:776329498
 * Class PayBank
 * @package mikkle\tp_wxpay\src
 */
class PayBank extends WxpayClientBase
{
    protected $url = "https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank";
    protected $publicKey;
    protected $useCaPath = true;  //使用根证书
    protected $bankCodeList =[1001,1002,1003,1004,1005,1006,1009,1010,1020,1021,1022,1025,1026,1027,1032,1056,1066];
    protected function checkParams()
    {
        if($this->params["partner_trade_no"] == null  )
        {
            throw new Exception("请设置付款 单号！"."<br>");
        }
        if($this->params["amount"] == null  )
        {
            throw new Exception("请设置付款 金额！"."<br>");
        }
        if ( isset($this->params["public_key"]  ) && !empty($this->params["public_key"]   ) ){
            $this->publicKey=$this->params["public_key"];
            unset( $this->params["public_key"] );
        }
        if(empty($this->publicKey ) )
        {
            throw new Exception("请设置正确的RSA公匙！"."<br>");
        }
        if(!isset( $this->params["bank_code"]  ) || $this->params["bank_code"] == null || !in_array( $this->params["bank_code"],$this->bankCodeList) )
        {
            throw new Exception("请设置正确的银行号bank_code！"."<br>");
        }
        if(!isset( $this->params["bank_no"]  ) || $this->params["bank_no"] == null  || $this->params["true_name"] == null )
        {
            throw new Exception("设置的帐号或者姓名不正确！"."<br>");
        }

        $this->params["enc_bank_no"] = Tools::encryptByRsa( $this->params["bank_no"] ,$this->publicKey);
        $this->params["enc_true_name"] = Tools::encryptByRsa( $this->params["true_name"] ,$this->publicKey);
        unset(  $this->params["bank_no"] );
        unset(  $this->params["true_name"] );
        if(empty( $this->params["enc_bank_no"]) || empty( $this->params["enc_true_name"]))
        {
            throw new Exception("RSA加密出错！"."<br>");
        }
    }

    function createXml()
    {
        $this->checkParams();
        $this->params["mch_id"] =   $this->options["mch_id"];           //config('wechat_mchid');//商户号
        $this->params["nonce_str"] = Tools::createNonceStr();//随机字符串
        $this->params["sign"] = Tools::getSignByKey($this->params,$this->options["key"]);//签名
        return  Tools::arrayToXml($this->params);
    }

    public function setRsaPublicKey($publicKey){
        if ($publicKey) {
            $this->publicKey = openssl_pkey_get_public( $publicKey );
        }
        return $this;
    }


    public function payToBankCard(){
        $this->getResultBySSLCurl();
        return $this->result;
    }


}