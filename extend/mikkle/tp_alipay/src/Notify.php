<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/7/11
 * Time: 21:30
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;

class Notify
{
    protected $options=[];
    public function __construct($options)
    {
        $this->setOptions($options);
        $this->_initialize();
    }
    protected function _initialize()
    {

    }

    protected function setOptions($options=[]){
        if ( !empty( $options)&& is_array( $options ) ){
            $this->options=  array_merge($this->options,$options);

        }else{
            throw  new  Exception("缺失重要的参数对象");
        }
        if (empty($this->options)){
            throw  new  Exception("参数缺失");
        }
    }

    public function verifyRsaSign($data){
        return Tools::verifyRsaSign($data ,$this->options["alipay_public_key"], isset($data["sign_type"] )? $data["sign_type"]: "RSA2");
    }



}