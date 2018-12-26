<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 17:38
 */

namespace mikkle\tp_wxpay\base;


use mikkle\tp_master\Exception;

class WxpayServerBase
{
    protected $data;
    protected $options;
    protected $appid;
    protected $secret;
    protected $mchId;
    protected $key;
    protected $certPath;
    protected $keyPath;
    protected $returnParams;//返回参数，类型为关联数组
    protected $xml;
    protected $autoSaveData =true; //收费自动转换存储xml

    public function __construct($options=[])
    {
        $this->xml = isset( $GLOBALS["HTTP_RAW_POST_DATA"] ) ? $GLOBALS["HTTP_RAW_POST_DATA"] : null;
        if ($options){
            $this->setOptions($options);
        }
        $this->_initialize();

        $this->autoSaveData && $this->savePostXmlDataAsArray();
    }
    public function _initialize()
    {

    }

    /**
 * 将微信的请求xml转换成关联数组，以方便数据处理
 */
    function savePostXmlDataAsArray()
    {
        if ($this->xml) {
            $this->data = Tools::xmlToArray($this->xml);
        } else {
            $this->data = null;
        }
    }

    /**
     * 将微信的请求xml转换成关联数组，以方便数据处理
     */
    public function getPostData()
    {
        return $this->data ;
    }

    public function checkSign(){
        if (empty($this->key) || empty($this->data)) return false;
        return Tools::checkSignByKey($this->data,$this->key);
    }

    protected function setOptions($options){
        if (isset($options["appid"]) &&isset($options["mch_id"]) &&isset($options["key"]) ){
            $this->options=empty($this->options)? $options :  array_merge($this->options,$options);
            $this->appid=$options["appid"];
            @$this->secret=$options["secret"];
            $this->mchId=$options["mch_id"];
            $this->key=$options["key"];
        }else{
            throw  new  Exception("缺失重要的参数对象");
        }
        if (isset($options["cert_path"])&&isset($options["key_path"])){
            $this->certPath=$options["cert_path"];
            $this->keyPath=$options["key_path"];
        }
        if (empty($this->options)){
            throw  new  Exception("参数缺失");
        }
    }


    /*
     * 设置返回微信的xml数据
     */
    function setReturnParam($param, $paramValue)
    {
        switch (true){
            case(is_string($param) &&( is_string($paramValue)||is_numeric($paramValue)) ):
                $this->returnParams[Tools::trimString($param)] = Tools::trimString($paramValue);
                break;
            case (is_array( $param) && empty( $paramValue)):
                foreach ($param as $item=>$value){
                    if (is_string($item) && ( is_string($value)||is_numeric($value))){
                        $this->returnParams[Tools::trimString($item)] = Tools::trimString($value);
                    }
                }
                break;
            default:
        }
        return $this;
    }

    /**
     * 生成接口参数xml
     */
    protected function createXml()
    {
        return Tools::arrayToXml($this->returnParams);
    }

    /**
     * 将xml数据返回微信
     */
    public function returnXml()
    {
        $returnXml = $this->createXml();
        return $returnXml;
    }


}