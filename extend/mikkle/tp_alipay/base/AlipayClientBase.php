<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/6/12
 * Time: 12:03
 */

namespace mikkle\tp_alipay\base;


use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

abstract class AlipayClientBase
{


    protected $method;
    //网关
    public $gatewayUrl = "https://openapi.alipay.com/gateway.do?";
    //返回数据格式
    public $debugInfo = false;

    protected $params=[];
    protected $paramList = ["app_id"];
    protected $options=[];
    protected $response;
    protected $result;
    protected $bizContent=[];
    protected $bizContentList=[];
    protected $requestList=[];

    public $error;

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

    /**
     * title 作用：设置请求参数 支持数组批量设置
     * description setParam
     * User: Mikkle
     * QQ:776329498
     * @param $param
     * @param string $paramValue
     * @return $this
     */
    function setParam($param, $paramValue="")
    {
        switch (true){
            case(is_string($param) &&( is_string($paramValue)||is_numeric($paramValue)) ):
                $this->params[Tools::trimString($param)] = Tools::trimString($paramValue);
                break;
            case (is_array( $param) && empty( $paramValue)):
                foreach ($param as $item=>$value){
                    if (is_string($item) && ( is_string($value)||is_numeric($value))){
                        $this->params[Tools::trimString($item)] = Tools::trimString($value);
                    }
                }
                break;
            default:
        }
        return $this;
    }
    function setBizContentParam($param, $paramValue="")
    {
        switch (true){
            case(is_string($param) &&( is_string($paramValue)||is_numeric($paramValue)) ):
                $this->bizContent[Tools::trimString($param)] = Tools::trimString($paramValue);
                break;
            case (is_array( $param) && empty( $paramValue)):
                foreach ($param as $item=>$value){
                    if (is_string($item) && ( is_string($value)||is_numeric($value))){
                        $this->bizContent[Tools::trimString($item)] = Tools::trimString($value);
                    }
                }
                break;
            default:
        }
        return $this;
    }

    protected function checkParams()
    {
        if ($this->paramList){
            foreach ( $this->paramList as $param ){
                if (!isset(  $this->params[$param]  )){
                    throw new Exception("缺少重要参数:[{$param}]");
                }
            }
        }
        if ($this->bizContentList){
            foreach ( $this->bizContentList as $bizContent ){

                if ( !isset(  $this->bizContent[$bizContent]  )){
                    throw new Exception("缺少重要参数:[{$bizContent}]");
                }
            }
        }
        $this->checkParamsHandle();
    }
     protected function checkParamsHandle(){

    }


    protected function buildPublicBizContentParam(){

    }

    protected function buildPublicParam(){
        $publicParam = [
            "method"=>$this->method,
            "app_id"=>$this->options["app_id"],
            "format"=>"JSON",
            "charset"=>"utf-8",
            "sign_type"=>"RSA2",
            "timestamp"=>date("Y-m-d H:i:s"),
            "version"=>"1.0",

        ];
        $this->params = array_merge($this->params ,$publicParam ) ;
    }
    protected function initParamsHandle(){
        $requestList = [];
        if (Tools::checkEmpty( $this->options["app_id"])){
            throw new Exception("缺少必备的app_id参数");
        }
        $requestList["app_id"] = $this->options["app_id"];
        if (Tools::checkEmpty( $this->method)){
            throw new Exception("缺少必备的method参数");
        }
        $this->buildPublicParam();
        $requestList=array_merge($requestList,$this->params);
        $this->buildPublicBizContentParam();
        $this->checkParams();
        $requestList["biz_content"]=json_encode($this->bizContent,JSON_UNESCAPED_UNICODE);
        $requestList["sign"]=$this->createRsaSign( $requestList);
        if (Tools::checkEmpty($requestList["sign"])){
            throw new Exception("签名失败");
        }
        $this->requestList = $requestList;
    }
    protected function getPostUrl(){
        if (!$this->requestList){
            throw new Exception("请求的缺少丢失");
        }
        $requestList = $this->requestList;
        return $this->gatewayUrl.Tools::formatBizQueryParaMap($requestList,true);
    }

    public function getResult(){
        try {
            $this->initParamsHandle();
            $this->response = Tools::curlPost($this->gatewayUrl,$this->requestList);
            $this->result = json_decode($this->response, true);

            if ( $this->result ==false){
                $this->response = mb_convert_encoding( $this->response, "UTF-8", "GBK");
                $this->result = json_decode($this->response, true);
            }
            $method_response = str_replace('.', '_', $this->method) . '_response';
            if ($this->result ===false){
                throw new Exception("获取支付宝 {$method_response} 信息失败");
            }elseif(isset($this->result[$method_response]['code']) && $this->result[$method_response]['code'] != '10000') {
                throw new Exception("{$this->result[$method_response]['sub_msg']}");
            }
            return $this->verify($this->result[$method_response], $this->result['sign'],true) ;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Log::error($e->getMessage());
            return false;
        }
    }

    public function getError(){
        return $this->error ;
    }

    /**
     * title
     * description createRsaSign
     * User: Mikkle
     * QQ:776329498
     * @param $params
     * @return string
     * @throws Exception
     */
    protected function createRsaSign($params){
        if (Tools::checkEmpty( $this->options["private_key"])){
            throw new Exception("缺少生成签名的私钥");
        }
        return Tools::getRsaSign($params, $this->options["private_key"],$this->params["sign_type"]);
    }


    public function verify($data, $sign = null, $sync = false)
    {
        if (!isset($this->options['alipay_public_key'])|| empty($this->options['alipay_public_key'])) {
            throw new Exception('Missing Config -- [public_key]');
        }
        $sign = is_null($sign) ? $data['sign'] : $sign;
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->options['alipay_public_key'], 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $toVerify = $sync ? json_encode($data) : Tools::formatBizQueryParaMap($data, false,["sign","sign_type"]);
        return openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }


    function echoDebug($content) {

        if ($this->debugInfo) {
            echo "<br/>" . $content;
        }

    }

    public function getResponse(){
        return $this->response;
    }
    public function getResponseArray(){
        return $this->result;
    }



}