<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/5/21
 * Time: 17:06
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

class RsaPublicKey extends WxpayClientBase
{

    protected $url = "https://fraud.mch.weixin.qq.com/risk/getpublickey";
    public function checkParams()
    {

    }

    /*
 * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
 */
    function createXml()
    {
        $this->checkParams();
        $this->params["mch_id"] =   $this->options["mch_id"];           //config('wechat_mchid');//商户号
        $this->params["nonce_str"] = Tools::createNonceStr();//随机字符串
        $this->params["sign"] = Tools::getSignByKey($this->params,$this->options["key"]);//签名
        return  Tools::arrayToXml($this->params);
    }

    function getRsaPublicKey()
    {
        $this->getResultBySSLCurl();
        return isset( $this->result["pub_key"]) ?  $this->result["pub_key"] : false ;
    }


}