<?php
namespace AmqpCall\lib;


use AMQPConnection;
use AMQPChannel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16
 * Time: 15:10
 */
class channel
{
    private static $instance;

    private $mode;
    private $channel = null;

    private function __construct($connect, $mode)
    {
        $this->mode = $mode;
        $this->channel = new AMQPChannel($connect);
        
        $this->setPrefetchCount(1);
    }

    private function __clone() {}

    static function getInstance(AMQPConnection $connect, $mode='')
    {
        if (empty(self::$instance)) {
            self::$instance = new self($connect, $mode);
        }

        return self::$instance;
    }

    public function getChannel()
    {
        return $this->channel;
    }


    //set prefetchCount
    public function setPrefetchCount($n)
    {
        $this->getChannel()->setPrefetchCount($n);
    }

    //set QPS

    //set transaction mode
    public function begin()
    {
        switch ($this->mode) {
            case 'transaction':
                $this->getChannel()->startTransaction();
                break;
            case 'confirm':
                $this->getChannel()->confirmSelect();
                break;
            default:
                break;
        }
    }

    public function commit()
    {
        switch ($this->mode) {
            case 'transaction':
                $this->getChannel()->commitTransaction();
                break;
            case 'confirm':

                break;
            default:
                break;
        }
    }

    public function rollback()
    {
        switch ($this->mode) {
            case 'transaction':
                $this->getChannel()->rollbackTransaction();
                break;
            case 'confirm':
                break;
            default:
                break;
        }
    }

    //set confirm mode
}