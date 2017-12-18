<?php
namespace AmqpCall\lib;

use AMQPChannel;
use AMQPExchange;
use AMQPExchangeException;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16
 * Time: 15:11
 */
class exchange
{
    private static $instance;

    private $excflag;
    private $exchange;

    private function __construct($channel)
    {
        $this->channel = $channel;
    }

    private function __clone() {}

    public static function getInstance(AMQPChannel $channel)
    {
        if (empty(self::$instance)) {
            self::$instance = new self($channel);
        }

        return self::$instance;
    }

    public function getExchange($exname, $extype, $exflags)
    {
        if (!password_verify(json_encode(func_get_args()), base64_decode($this->excflag))) {
            $this->excflag = base64_encode(password_hash(json_encode(func_get_args()), PASSWORD_BCRYPT));
            try {
                $this->exchange = new AMQPExchange($this->channel);
                empty($exname) OR $this->exchange->setName($exname);
                empty($extype) OR $this->exchange->setType($extype);
                empty($exflags) OR $this->exchange->setFlags($exflags);
                empty($exname) OR $this->exchange->declareExchange();
            } catch(AMQPExchangeException $e) {
                die('falgs change '.$e->getMessage());
            }
        }

        return $this->exchange;
    }
}