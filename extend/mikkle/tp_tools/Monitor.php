<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\6\21 0021
 * Time: 17:08
 */

namespace mikkle\tp_tools;


use mikkle\tp_master\Exception;

class Monitor
{
    protected $ip ;
    protected $port;
    protected $url;
    protected $error;
    protected $params;
    protected $result;


    public function __construct($ip,$port=80)
    {
        $this->ip = $ip ;
        $this->port = $port ;
    }
    public static function instance($ip,$port=80)
    {
        return  new static($ip,$port);
    }


    public function setIP($value)
    {
        $this->ip = $value;
        return $this;
    }
    public function setPort( $value )
    {
        $this->port = $value;
        return $this;
    }

    public function runHandle(){
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock,$this->ip, $this->port);
        socket_set_block($sock);
        $r = array($sock);
        $w = array($sock);
        $f = array($sock);
        $this->result = socket_select($r, $w , $f, 5);
        return($this->result);
    }
    public function checklist($lst){
    }
    public function status(){
        switch($this->result)
        {
            case 2:
                echo "Closed\n";
                break;
            case 1:
                echo "Openning\n";
                break;
            case 0:
                echo "Timeout\n";
                break;
        }
    }
    public function getResult(){
        $this->runHandle();
        return $this->result;
    }



}