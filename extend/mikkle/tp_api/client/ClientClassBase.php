<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\6\15 0015
 * Time: 11:11
 */

namespace mikkle\tp_api\client;
use mikkle\tp_master\Exception;

/**
 * Class ClientClassBase
 * @package mikkle\tp_api\client
 */
class ClientClassBase
{

    protected $options;
    protected $params;
    protected $response; //
    protected $result;
    protected $error;


    public function __construct($options=[])
    {
        $this->options = [];
        $this->_initialize();
    }
    public function _initialize()
    {

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
                $this->params[$param] = self::trimString($paramValue);
                break;
            case (is_array( $param) && empty( $paramValue)):
                foreach ($param as $item=>$value){
                    if (is_string($item) && ( is_string($value)||is_numeric($value))){
                        $this->params[$item] = self::trimString($value);
                    }
                }
                break;
            default:
        }
        return $this;
    }


    static public function trimString($value)
    {
        $ret = null;
        if (null != $value)
        {
            $ret = $value;
            if (strlen($ret) == 0)
            {
                $ret = null;
            }
        }
        return $ret;
    }
    static public function getSafeValue($value,$default = null){
        if (!isset( $value )||is_array($value )) return $default;
        if (empty($value)&&(string)$value!==0) return $default;
        return trim($value);
    }

    public function getResponse(){
        return $this->response;
    }


    public function getOptions()
    {
        return $this->options;
    }
    public function getError(){
        return $this->error;
    }

    /*
* 	作用：array转xml
*/
    static public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<{$key}>{$val}</{$key}>";
            }
            else
                $xml.="<{$key}><![CDATA[{$val}]]></{$key}>";
        }
        $xml.="</xml>";
        return $xml;
    }

    /*
 * 	作用：将xml转为array
 */
    static public function xmlToArray($xml)
    {
        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }


    /*
 * 	作用：格式化参数，签名过程需要使用
 */
    static public function formatParamToString($params, $urlencode=false)
    {
        $buff = "";
        ksort($params);
        foreach ($params as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= "&{$k}={$v}";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 1);
        }
        return $reqPar;
    }


    static public function getSignByMd5($data,$keyString="",$urlencode=false,$noSignFields =[]){
        if (!is_array($data)){
            throw  new  Exception("加密签名的必须为数组");
        }
        if (!empty( $noSignFields)){
            foreach ( $noSignFields as $field){
                unset($data[$field]);
            }
        }
        return Md5(self::formatParamToString($data,$urlencode).$keyString);
    }

    static public function getSignByRsa($data,$priKey,$urlencode=false,$noSignFields =[]){
        if (!is_array($data)){
            throw  new  Exception("加密签名的必须为数组");
        }
        if (!empty( $noSignFields)){
            foreach ( $noSignFields as $field){
                unset($data[$field]);
            }
        }
        $ret = false;
        $digest = openssl_digest(self::formatParamToString($data,$urlencode), "md5");

        if (openssl_sign($digest, $ret, $priKey)){
            $ret = base64_encode(''.$ret);
        }

        return $ret;
    }




}