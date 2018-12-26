<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/3
 * Time: 17:25
 */

namespace app\base\service;


use app\base\base\RedisInfoBase;
use app\base\options\Fields;
use mikkle\tp_wxpay\Wxpay;
use think\Exception;
use think\facade\Config;
use think\facade\Log;

/**
 * title  使用的 RedisHashInfoBase 基类的支付service类库
 * User: Mikkle
 * QQ:776329498
 * Class ChannelCenter
 * @package app\base\service
 */
class ChannelCenterDemo extends RedisInfoBase
{
    protected $table="mk_payment_channel";  //数据表的
    protected $pk = "channel_id"; //数据表的主键

    protected $debugHost = "http://pay.mikkle.cn";
    protected $releaseHost = "http://pay.mikkle.cn";
    protected $wxpay;
    protected $wxpayUnifiedOrder;
    protected $wxpayJsapi;
    protected $wxpayNotifyUrl ="/weixin/api.callback/callbackWxPay";
    protected $wxpayError;
    protected $isDebug=true;

    public function _initialize()
    {
        $this->initLoadTableData();
    }

    public function initLoadTableData(){
        //判断数据存在 并设置检查周期10分钟
        if (!$this->checkLock("dataExists") && !$this->checkTableDataExists()){
            throw  new  Exception("相关渠道数据不存在");
        }else{
            //设置检查锁10分钟
            $this->setLock("dataExists",600);
        }
        //如果数据不存在 初始化读取数据
        if (!$this->checkExists()){
            $this->initTableData();
        }
    }

    public function getWxpayOptions(){
        $options = $this->getInfoList([
            "wxpay_app_id" ,"wxpay_secret","wxpay_mch_id","wxpay_key","wxpay_cert_info","wxpay_cert_key_info","wxpay_callback_url"
        ]);
        return empty( $options["wxpay_app_id"] ) ?[
            "appid"=>$options["wxpay_app_id"],
            'secret' => $options["wxpay_secret"],
            'mch_id'=>$options["wxpay_mch_id"],
            'key'=>$options["wxpay_key"],
            'cert_path'=>$options["wxpay_cert_info"],
            'key_path'=>$options["wxpay_cert_key_info"],
            "callback_url"=>$options["wxpay_callback_url"],
        ] : [ ] ;
    }

    protected function getWechatOptions(){
        $options = $this->getInfoList([
            "wxpay_app_id" ,"wxpay_secret"
        ]);
        return [
            'appid'=>$options["wxpay_app_id"],
            'appsecret'=>$options["wxpay_secret"],
        ];
    }

    public function wxpay(){
        if (isset($this->wxpay )){
            return $this->wxpay;
        }
        $this->wxpay = Wxpay::instance($this->getWxpayOptions());
        return $this->wxpay ;
    }

    public function wxpayUnifiedOrder(){
        if (isset($this->wxpayUnifiedOrder)){
            return $this->wxpayUnifiedOrder;
        }
        $this->wxpayUnifiedOrder =$this->wxpay()->unifiedOrder();
        return $this->wxpayUnifiedOrder;
    }
    public function wxpayJsapi(){
        if (isset($this->wxpayJsapi)){
            return $this->wxpayJsapi;
        }
        $this->wxpayJsapi =$this->wxpay()->jsApi();
        return $this->wxpayJsapi;
    }

    protected function getWxpayNotifyUrl(){
        $url = (Config::get("app_status")=="debug" ? $this->debugHost :$this->releaseHost) . $this->wxpayNotifyUrl;
        return $url ."/".Fields::$channelId."/".$this->infoId;
    }

    public function getWxpayPrepayId($param){
        $param["body"]=" ";
        $param["notify_url"] = $this->getWxpayNotifyUrl();
        $this->isDebug && Log::notice($param);
        $prepayId =  $this->wxpayUnifiedOrder()->setParam($param )->getPrepayId();
        if ($prepayId == false){
            $this->isDebug && Log::notice($this->wxpayUnifiedOrder()->getResponse());
            $this->wxpayError =  $this->wxpayUnifiedOrder()->getResponseMsg() ;
        }
        return $prepayId;
    }

    public function getWxpayPayUrl($param,$type="MWEB"){
        if ($type =="NATIVE"){
            $param["trade_type"] ="NATIVE";
        }else{
            $param["trade_type"] ="MWEB";
        }
        $param["body"]=" ";
        $param["notify_url"] = $this->getWxpayNotifyUrl();
        $this->isDebug && Log::notice($param);
        $result =  $this->wxpayUnifiedOrder()->setParam($param )->getPayUrl();
        if ($result == false){
            $this->isDebug && Log::notice($this->wxpayUnifiedOrder()->getResponse());
            $this->wxpayError =  $this->wxpayUnifiedOrder()->getResponseMsg() ;
        }
        return $result;
    }

    public function getWxpayGetJsapiPayParams($param){
        $param["trade_type"]="JSAPI" ; /**交易类型  NATIVE  MWEB  JSAPI*/
        $prepayId = $this->getWxpayPrepayId( $param );
        if ( $prepayId ==false ){
            return false ;
        }
        $payParams = $this->wxpayJsapi()->getJsPayParamsByPrepayId($prepayId) ;
        if ($payParams == false){
            $this->wxpayError =  $this->wxpayUnifiedOrder()->getResponseMsg() ;
        }
        return $payParams;
    }

    public function getWxpayError(){
        return $this->wxpayError;
    }





}