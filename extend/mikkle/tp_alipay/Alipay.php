<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/6/12
 * Time: 11:22
 */

namespace mikkle\tp_alipay;


use mikkle\tp_alipay\base\Tools;
use mikkle\tp_alipay\src\Notify;
use mikkle\tp_alipay\src\PagePay;
use mikkle\tp_alipay\src\Precreate;
use mikkle\tp_alipay\src\Query;
use mikkle\tp_alipay\src\Refund;
use mikkle\tp_alipay\src\Transfer;
use mikkle\tp_alipay\src\WapPay;
use mikkle\tp_master\Config;
use mikkle\tp_master\Exception;

class Alipay
{

    static protected $instance;
    protected $options=[];
    static protected $optionsList =["app_id","alipay_public_key","private_key","public_key"];
    public function __construct($options=[])
    {
        $this->options= $this->getOptions($options) ;

    }

    public static function instance($options=[])
    {
        $sn = self::getSn($options) ;
        if (isset(self::$instance[$sn])){
            return self::$instance[$sn];
        }
        return  self::$instance[$sn]=new static($options);
    }

     public function WapPay($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new WapPay($options);
        }
    }
    public function PagePay($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new PagePay($options);
        }
    }

    public function Transfer($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new Transfer($options);
        }
    }

    public function Notify($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new Notify($options);
        }
    }


    public function Precreate($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new Precreate($options);
        }
    }

    public function Query($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new Query($options);
        }
    }

    public function Refund($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn."_".__FUNCTION__])){
            return self::$instance[$sn."_".__FUNCTION__];
        }else{
            return self::$instance[$sn."_".__FUNCTION__] = new Refund($options);
        }
    }




    protected static function getSn($options)
    {
        if (self::checkOptions($options)){
            is_array($options)&&ksort( $options );
            $string = "";
            foreach ( self::$optionsList as $value ){
                $string.=trim( $options[$value] );
            }
            return md5( $string );
        }
        return 0 ;

    }

    static  protected function checkOptions($options){
        foreach ( self::$optionsList as $value ){
            if ( Tools::checkEmpty( $options[$value] )){
                return false;
            }
        }
        return true;
    }


    protected   function getOptions( $options = []){
        if (!empty($this->options)&&empty($options)){
            $options=$this->options;
        }elseif (empty($options) && !empty( Config::get("alipay.default_options_name"))){
            $options = Config::get("alipay.".Config::get("alipay.default_options_name"));
        }elseif(is_string($options)&&!empty( Config::get("alipay.$options"))){
            $options = Config::get("alipay.$options");
        }
        if (empty($options)&&empty($this->options)) {
            $error[]="微信支付配置参数缺失";
            throw new Exception("支付宝支付配置参数不存在");
        }elseif(self::checkOptions($options)){
            return $options ;
        }else{
            if (!self::checkOptions($this->options) ){
                throw new Exception("微信支付配置参数不完整");
            }
        }
    }
}