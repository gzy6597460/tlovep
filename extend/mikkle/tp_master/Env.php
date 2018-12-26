<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/5/23
 * Time: 20:45
 */

namespace mikkle\tp_master;


use think\Facade;

class Env extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Env';
    }

}