<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 17:56
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_wxpay\base\WxpayServerBase;

/**
 * title 使用默认继承的方法就够了
 * User: Mikkle
 * QQ:776329498
 * Class Notify
 * @package mikkle\tp_wxpay\src
 */
class Notify extends WxpayServerBase
{

    /*
     * 将xml数据返回微信
     */
    public function returnResult($result = true)
    {
        header("Content-type: text/xml");
        if ($result){
            $this->setReturnParam("return_code","SUCCESS");
        }else{
            $this->setReturnParam("return_code","FAIL");
        }
        return $this->createXml();
    }


}