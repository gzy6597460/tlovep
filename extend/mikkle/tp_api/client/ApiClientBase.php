<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\6\15 0015
 * Time: 10:52
 */

namespace mikkle\tp_api\client;
use mikkle\tp_master\Exception;

/**
 *
 * Class ApiClientBase
 * @package mikkle\tp_api\client
 */
abstract class ApiClientBase extends ClientClassBase
{


    protected $submitType = "POST";
    protected $postFields;
    protected $paramsType = false ; //Json Xml Form
    protected $isSSL = false;
    protected $certPath;
    protected $keyPath;
    protected $caPath;
    protected $signType = false ;   //md5 MD5ENCODE rsa RSAENCODE
    protected $signKey;
    const SIGN_TYPE_VALUE_MD5 = "MD5";
    const SIGN_TYPE_VALUE_MD5ENCODE="MD5ENCODE";
    const SIGN_TYPE_VALUE_RSA = "RSA";
    const SIGN_TYPE_VALUE_RSAENCODE = "RSAENCODE";
    protected $rsaPrivateKey ;
    protected $signFieldName = "sign"  ;
    protected $timeOut = 30 ;
    protected $isProxy = false ;
    protected $proxy;
    protected $proxyPort;
    protected $requireParams;  //必填参数
    protected $url;  //请求的URL


    abstract protected function checkParams();


    protected function beforeSubmitHandler(){

    }
    public function runCurlParamsHandle()
    {
        $ch = curl_init();
        try {
            //超时时间
            curl_setopt($ch, CURLOPT_TIMEOUT, self::getSafeValue($this->timeOut, 30));

            if ($this->isProxy) {            //这里设置代理，如果有的话
                curl_setopt($ch, CURLOPT_PROXY, self::getSafeValue($this->proxy, '8.8.8.8'));
                curl_setopt($ch, CURLOPT_PROXYPORT, self::getSafeValue($this->proxyPort, 8080));
            }
            if ($this->isSSL) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA颁布的证书
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
                //设置证书
                //使用证书：cert 与 key 分别属于两个.pem文件
                //默认格式为PEM，可以注释
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLCERT, $this->certPath);
                //默认格式为PEM，可以注释
                curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLKEY, $this->keyPath);
                if ($this->caPath) {
                    curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
                    curl_setopt($ch, CURLOPT_CAINFO, $this->caPath);
                }
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
            }

            //设置header
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            //要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


            if (strtoupper($this->submitType) == "POST"){
                //post提交方式
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getSubmitFields());
            }elseif (strtoupper($this->submitType) == "GET" && !empty($this->params)){
                $this->url =$this->url."?".$this->getSubmitFields();
            }
            curl_setopt($ch, CURLOPT_URL, $this->url);
            if (strtoupper($this->submitType) == "JSON"){
                $header= [
                    'Content-Type: application/json',
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }


            $this->response = curl_exec($ch);
            //返回结果
            if ($this->response) {
                curl_close($ch);
                return true;
            } else {
                $this->error = "curl出错，错误码:" . curl_errno($ch);
                curl_close($ch);
                return false;
            }

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    protected function getSubmitFields(){
        switch (true){
            case (strtoupper($this->paramsType) == "XML"):
                $this->postFields = self::arrayToXml($this->params);
                break;
            case (strtoupper($this->paramsType) == "JSON"):
                $this->postFields = json_encode($this->params);
                break;
            default:
                $aPOST = array();
                foreach($this->params as $key=>$val){
                    $aPOST[] = $key."=".urlencode($val);
                }
                $this->postFields =  join("&", $aPOST);;
        }
        return $this->postFields;
    }

    /**
     * 提交数据
     */
    protected function submitParams()
    {
        try{
            $this->checkParams();
            if (empty($this->url)){
                throw new Exception("缺少必备参数 [ url ]!");
            }
            if ($this->requireParams){
                foreach ($this->requireParams as $param){
                    if (!in_array($param,$this->params )){
                        throw new Exception("缺少必备参数 [{$param}]!");
                    }
                }
            }
            $this->createSign();
            $this->beforeSubmitHandler();
            return $this->runCurlParamsHandle();
        }catch (Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }

    public function setUrl($url){
        $this->url = $url;
        return $this;
    }

    protected function createSign(){
        if ($this->signType && $this->signFieldName){
            switch (true){
                case (strtoupper($this->signType) == "MD5"):
                    $this->params[$this->signFieldName] = self::getSignByMd5($this->params,$this->signKey);
                    break;
                case (strtoupper($this->signType) == "MD5ENCODE"):
                    $this->params[$this->signFieldName] = self::getSignByMd5($this->params,$this->signKey,true);
                    break;
                case (strtoupper($this->signType) == "RSA"):
                    if ($this->rsaPrivateKey){
                        $this->params[$this->signFieldName] = self::getSignByRsa($this->params,$this->rsaPrivateKey);
                    }
                    break;
                case (strtoupper($this->signType) == "RSAENCODE"):
                    if ($this->rsaPrivateKey) {
                        $this->params[$this->signFieldName] = self::getSignByRsa($this->params, $this->rsaPrivateKey, true);
                    }
                    break;
                default:
            }
            $this->customSign();
        }
    }
    protected function customSign(){

    }

    public function getResult()
    {
        if ($this->submitParams()){
            switch (true){
                case (strtoupper($this->paramsType) == "XML"):
                    $this->result = self::xmlToArray($this->response);
                    break;
                case (strtoupper($this->paramsType) == "JSON"):
                    $this->result = json_decode($this->response);
                    break;
                default:
                    $this->result = $this->response;
            }
            return $this->result;
        }
        return false;
    }


}