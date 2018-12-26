<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\6\15 0015
 * Time: 16:29
 */

namespace app\api\controller;




use mikkle\tp_alipay\Alipay;
use think\Controller;
use think\facade\Log;

class demo extends Controller
{

    public function PagePay()
    {
        $options =[
            "app_id"=>"20180610603***87",
            "public_key"=>"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwp4lJJtFWVE0ioP8OLVk

aQIDAQAB
-----END PUBLIC KEY-----",
            "alipay_public_key"=>"MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo1MIUWLkFfWJSGRPAey7iJ9xxnqH4MEoDzzz15Becj/ophVIwJi9cmpq7ABGu9tNuA/gmDPuig4US6RVrs8T+knCMjRzdMZk7VRwl/fmJJrhTKxhgUqLkaKuc85Yxmr8+N78ekc7bs/viZY+THygR3TSsOr+ilJcHgY7Lm0bhWFIKzPvBnvMyVWicrJJfgYf/cAm2jk3TJF9KDUiQoLy6jDDJqHqqORUAz2yzYK0+mKgYV6CR0F7m2UMKjywMlSOliPH0CC520Lf0HA8yHeSVowOYmqkVvt4JAXQHi4pNXifPIFWN7tILmh3bLNA4FCwZHGWSFoMe8sVXmJ0rT+HWQIDAQAB",
            "private_key"=>"-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDCniUkm0VZUTSK
g/w4tWReYr3d0zTGZM+ZUNAx2ycucutmOaZdXAY8Fw5MHSje20KavEjU2CVEBcPr
iwwU1xWSbuurJ077IALxk3O9uMNNzhH+yBRGtoyQF1HSoYL6RtC5TsDeOJKj6Oda
C+jb0BngpZ1f8JT3QY1gezw/9Hgwy9QG9AkgvVFFyoQQrXBmqHqnkZEmaxWxXyuO

4nOD8DrQ7V4rwqLw99HB9Gc=
-----END PRIVATE KEY-----",
        ];
         dump(  Alipay::instance($options)->PagePay()
            ->setParam([
                "return_url"=>"http://paycenter.pay.cn/api/test/request/asdsad",
                "notify_url"=>"http://paycenter.pay.cn/api/test/request"
            ])
            ->setBizContentParam([
                "subject"=>"debug",
                "out_trade_no"=>(string)time(),
                "total_amount"=>"0.01",
            ])
            ->getQuickPayUrl() );

    }


    public function wapPay()
    {
        $options =[
            "app_id"=>"20180610603***87",
            "public_key"=>"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwp4lJJtFWVE0ioP8OLVk

aQIDAQAB
-----END PUBLIC KEY-----",
            "alipay_public_key"=>"MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo1MIUWLkFfWJSGRPAey7iJ9xxnqH4MEoDzzz15Becj/ophVIwJi9cmpq7ABGu9tNuA/gmDPuig4US6RVrs8T+knCMjRzdMZk7VRwl/fmJJrhTKxhgUqLkaKuc85Yxmr8+N78ekc7bs/viZY+THygR3TSsOr+ilJcHgY7Lm0bhWFIKzPvBnvMyVWicrJJfgYf/cAm2jk3TJF9KDUiQoLy6jDDJqHqqORUAz2yzYK0+mKgYV6CR0F7m2UMKjywMlSOliPH0CC520Lf0HA8yHeSVowOYmqkVvt4JAXQHi4pNXifPIFWN7tILmh3bLNA4FCwZHGWSFoMe8sVXmJ0rT+HWQIDAQAB",
            "private_key"=>"-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDCniUkm0VZUTSK
g/w4tWReYr3d0zTGZM+ZUNAx2ycucutmOaZdXAY8Fw5MHSje20KavEjU2CVEBcPr
iwwU1xWSbuurJ077IALxk3O9uMNNzhH+yBRGtoyQF1HSoYL6RtC5TsDeOJKj6Oda
C+jb0BngpZ1f8JT3QY1gezw/9Hgwy9QG9AkgvVFFyoQQrXBmqHqnkZEmaxWxXyuO

4nOD8DrQ7V4rwqLw99HB9Gc=
-----END PRIVATE KEY-----",
        ];
        dump(  Ali );

    }


    function request(){
        Log::error($this->request->request());
dump($this->request->request() );
    }
}