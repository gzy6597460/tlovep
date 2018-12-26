<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\6\15 0015
 * Time: 16:13
 */

namespace app\base\service;


use mikkle\tp_api\client\ApiClientBase;

class Pay extends ApiClientBase
{

    protected $signKey = "6e713989cec4cdj668";
    protected $submitType = "GET" ;
    protected function checkParams()
    {
        // TODO: Implement checkParams() method.

    }
    protected function beforeSubmitHandler(){
        $this->params["sign"] = md5(self::formatParamToString($this->params,true)."key={$this->signKey}");

        
    }
}