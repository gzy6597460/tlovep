<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/5/18
 * Time: 16:09
 */

class index
{

    /**
     * title 获取扫码支付地址
     * description getPayUrl
     * User: Mikkle
     * QQ:776329498
     * @return mixed
     */
    public function getPayUrl(){

        $resultData = [
            'body' => "{\"h5_info\": {\"type\":\"Wap\",\"wap_url\": \"https://pay.qq.com\",\"wap_name\": \"腾讯充值\"}} ", /**商品描述*/
            'out_trade_no' => time(), /**商户订单号*/
            'total_fee' => 1, /**标价金额(单位分)*/
            'notify_url' => url(), /**通知地址(WchatConfig::$notificationURL)*/
            'trade_type' => "NATIVE", /**交易类型  NATIVE  MWEB  */
        ];
        $result = \mikkle\tp_wxpay\Wxpay::instance()->unifiedOrder()->setParam($resultData)->getPayUrl();
        return $result;
    }

    /**
     * title 获取H5支付地址
     * description getPayUrl
     * User: Mikkle
     * QQ:776329498
     * @return mixed
     */
    public function getMWEBPayUrl(){
        $resultData = [
            'body' => "{\"h5_info\": {\"type\":\"Wap\",\"wap_url\": \"https://pay.qq.com\",\"wap_name\": \"腾讯充值\"}} ", /**商品描述*/
            'out_trade_no' => time(), /**商户订单号*/
            'total_fee' => 1, /**标价金额(单位分)*/
            'notify_url' => url(), /**通知地址(WchatConfig::$notificationURL)*/
            'trade_type' => "MWEB", /**交易类型  NATIVE  MWEB  */
        ];
        $result = \mikkle\tp_wxpay\Wxpay::instance()->unifiedOrder()->setParam($resultData)->getPayUrl();
        return $result;
    }


    /**
     * title 获取JSAPI支付加密串
     * description getWxPayParams
     * User: Mikkle
     * QQ:776329498
     * @param $data
     * @return array
     * @throws Exception
     */
    public function getWxPayParams($data){
        header('Access-Control-Allow-Origin:*');
        $this->args = $data;
        $this->functionName = __FUNCTION__;
        $fieldList = [Fields::$systemId,Fields::$channelType,Fields::$vendorId,Fields::$billMoney,Fields::$billNum,Fields::$trueIp];
        if (!$this->checkArrayValueStatus($data,$fieldList) ){
            return ShowCode::jsonCodeWithoutData(1003,$this->error);
        }
        $channelId = DataCenter::getChannelIdBySystemChannelType( $data );
        $channelCenter = ChannelCenter::instance($channelId) ;

        $wxBillNum = Rands::createBillSerialNumber();
        $paramList = [
            'out_trade_no' => $wxBillNum, /**商户订单号*/
            'total_fee' => $data[Fields::$billMoney], /**标价金额(单位分)*/
            'trade_type' => "JSAPI", /**交易类型  NATIVE  MWEB  JSAPI*/
            'openid'=>$data["openid"],
            'spbill_create_ip'=>$data[Fields::$trueIp],
        ];
        //这里还要判断重复

        $result =  $channelCenter->getWxpayGetJsapiPayParams($paramList);
        if ($result == false){
            throw  new Exception( $channelCenter->getWxpayError());
        }
        $data["channel_id"] =$channelId;
        $data["prepay_id"] = ltrim( $result["package"]  ,"prepay_id=");
        $data["out_trade_no"] =$wxBillNum;
        $billCenter = BillCenter::instance( $wxBillNum );
        $billCenter->setInfoArray( $data ) ;
        $billCenter->updateTableData();
        $this->result =$result;
        return ShowCode::jsonCode(1001,$result);
    }


    /**
     * title 微信回调接收接口
     * description callbackWxPay
     * User: Mikkle
     * QQ:776329498
     * @return array|string
     */
    public function callbackWxPay(){
        try{
            $channelId = $this->request->param(Fields::$channelId);
            $options = ChannelCenter::instance( $channelId )->getWxpayOptions();
            $callback = Wxpay::instance($options)->Notify();
            $result= $callback->getPostData();
            $result["sign_status"] = $callback->checkSign() ? 1 : 0 ;
            return $callback->returnResult(WxpayCallbackLogic::instance()->callbackWxPayHandle($result) ? true : false );
        }catch (Exception $e){
            Log::error($e->getMessage());
            return ShowCode::jsonCodeWithoutData(1008,$e->getMessage());
        }
    }

    /**
     * title付款到微信和银行
     * description pay
     * User: Mikkle
     * QQ:776329498
     */
    function pay(){
        $channelCenter =  ChannelCenter::instance("20000001");

        $payBank = $channelCenter->wxpay()->Paybank();
        $payBank->setRsaPublicKey( $channelCenter->getRsaPublicCert() );
        dump(  $payBank->setParam([
            "partner_trade_no"=>time(),
            "bank_no"=>"9555507556395923",
            "true_name"=>"何大鹏",
            "bank_code"=>"1001",
            "amount"=>1,
            "desc"=>"测试"
        ])->payToBankCard() );


        /**
         * 付款到银行
         */
        $channelCenter =  ChannelCenter::instance("20000001");

        $payBank = $channelCenter->wxpay()->Transfers();

        dump(  $payBank->setParam([
            "partner_trade_no"=>time(),
            "openid"=>"oYk0SuAT_HujzoLSzaOHKW17Lda4",
            "check_name"=>"NO_CHECK",
            "amount"=>1,
            "desc"=>"测试"
        ])->payToUser() );


    }

}