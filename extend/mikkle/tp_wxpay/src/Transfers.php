<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/5/21
 * Time: 16:00
 */

namespace mikkle\tp_wxpay\src;
use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

/**
 * title  企业付款接口
 * User: Mikkle
 * QQ:776329498
 * Class Transfers
 * @package mikkle\tp_wxpay\src
 */
class Transfers extends WxpayClientBase
{
    protected  $url ="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
    protected $useCaPath = true;  //使用根证书

    public function _initialize()
    {

    }

    protected function checkParams()
    {
        //检测必填参数
        if(!isset( $this->params["partner_trade_no"]  ) || $this->params["partner_trade_no"] == null  )
        {
            throw new Exception("请设置付款 单号[partner_trade_no]！"."<br>");
        }
        if(!isset( $this->params["amount"]  ) || $this->params["amount"] == null  )
        {
            throw new Exception("请设置付款 金额  [amount]！"."<br>");
        }
        if(!isset( $this->params["openid"]  ) || $this->params["openid"] == null  )
        {
            throw new Exception("请设置付款  [openid]！"."<br>");
        }

        if(!isset( $this->params["check_name"]  ) || $this->params["check_name"] == null  )
        {
            throw new Exception("请设置付款 是否校验 [check_name]！"."<br>");
        }
        if (!isset( $this->params["check_name"]  ) || $this->params["check_name"] =="FORCE_CHECK"){
            if(!isset( $this->params["re_user_name"]  ) || $this->params["re_user_name"] == null  )
            {
                throw new Exception("请设置付款 姓名 [re_user_name]！"."<br>");
            }
        }

        if(!isset( $this->params["desc"]  ) ||$this->params["desc"] == null  )
        {
            throw new Exception("请设置付款 备注 [desc]！"."<br>");
        }
        if(!isset( $this->params["spbill_create_ip"]  ) || $this->params["spbill_create_ip"] == null  )
        {
            $this->params["spbill_create_ip"] = Tools::getRealIp();
        }
    }

    function createXml()
    {
        $this->checkParams();
        $this->params["mch_appid"] = $this->options["appid"];                  //公众账号ID
        $this->params["mchid"] =   $this->options["mch_id"];           //config('wechat_mchid');//商户号
        $this->params["nonce_str"] = Tools::createNonceStr();//随机字符串
        $this->params["sign"] = Tools::getSignByKey($this->params,$this->options["key"]);//签名
        return  Tools::arrayToXml($this->params);
    }

    public function payToUser(){
        $this->getResultBySSLCurl();
        return $this->result;
    }

}