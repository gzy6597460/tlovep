<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/6/12
 * Time: 12:03
 */

namespace mikkle\tp_alipay\base;


use think\facade\Log;

class Tools
{
    static public 	 function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
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

    static public function checkArrayValueEmpty($array,$value){
        switch (true){
            case (empty($array)||!is_array($array)):
                return false;
                break;
            case (is_array($value)):
                foreach ($value as $item){
                    if (self::checkEmpty( $array[$item] )){
                        return false;
                    }
                }
                break;
            case (is_string($value)):
                if (self::checkEmpty( $array[$value] )){
                    return false;
                }
                break;
            default:
        }
        return true;
    }

    /*
 * 	作用：格式化参数，签名过程需要使用
 */
    static public function formatBizQueryParaMap($paraMap, $urlencode=false,$except=["sign"])
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if (!in_array( $k, $except )){
                if($urlencode)
                {
                    $v = urlencode($v);
                }
                $buff .= "&{$k }={$v }";
            }
            }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 1);
        }
        return $reqPar;
    }

    static public function getRsaSign($data,$privateKey,$signType="RSA2"){
        if (is_array( $data )){
            $data = self::formatBizQueryParaMap($data);
        }
        $privateKey = self::checkRsaPrivateKey($privateKey);
        // 签名
        $signature = '';
        if("RSA2"== $signType){
            openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256 );
        }else{
            openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA1 );
        }
        return base64_encode( $signature);
    }


    static public function checkRsaPrivateKey($privateKey){
        if (strpos($privateKey,"-----BEGIN PRIVATE KEY-----")!==false && strpos($privateKey,"\n")!==false){
            return $privateKey;
        }
        // 私钥密码
        $key_width = 64;
        //私钥
        $p_key = array();
        //如果私钥是 1行
        $privateKey = str_replace(array("\r\n", "\r", "\n","-----BEGIN PRIVATE KEY-----","-----END PRIVATE KEY-----"), "", $privateKey);
        $i = 0;
        while( $key_str = substr( $privateKey , $i * $key_width , $key_width) ){
            $p_key[] = $key_str;
            $i ++ ;
        }
        return "-----BEGIN PRIVATE KEY-----\n" . implode("\n", $p_key)."\n-----END PRIVATE KEY-----" ;
    }

    static public function verifyRsaSign($data,$publicKey,$signType="RSA2",$signName = "sign"){
        if (!isset( $data[$signName] )){
            return false;
        }
        $sign = str_replace(" ","+", trim( $data[$signName] ) )  ;  unset( $data[$signName]  );

       if (isset($data["sign_type"]  )) unset( $data["sign_type"]  );
            $data = self::formatBizQueryParaMap($data);
        $publicKey = self::checkRsaPublicKey($publicKey);

        if("RSA2"== $signType){
           $result =  openssl_verify($data, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256) ;
        }else{
            $result = openssl_verify($data, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA1 );
        }

        return $result===1 ? true : false ;
    }


    static public function checkRsaPublicKey($publicKey){
        if (strpos($publicKey,"-----BEGIN PUBLIC KEY-----")!==false && strpos($publicKey,"\n")!==false){
            return $publicKey;
        }
        // 私钥密码
        $key_width = 64;
        //私钥
        $p_key = array();
        //如果私钥是 1行
        $publicKey = str_replace(array("\r\n", "\r", "\n","-----BEGIN PUBLIC KEY-----","-----END PUBLIC KEY-----"), "", $publicKey);
        $i = 0;
        while( $key_str = substr( $publicKey , $i * $key_width , $key_width) ){
            $p_key[] = $key_str;
            $i ++ ;
        }
        return "-----BEGIN PUBLIC KEY-----\n" . implode("\n", $p_key)."\n-----END PUBLIC KEY-----" ;
    }


    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @param int $timeout
     * @return string content
     */
    static public function curlPost($url,$param,$post_file=false,$timeout = 30){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else{
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }

        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_TIMEOUT,$timeout);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
//        dump(json_decode($sContent));
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }


}