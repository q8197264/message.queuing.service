<?php
namespace Cache\Core\Storage\Database;
/**
 * 数据操作类
 * 负责 操作数据库的 CURD
 * User: Liu xiao quan
 * Date: 2016/7/13
 * Time: 11:21
 */
class Mysqli
{
    private static $db;

    function __construct()
    {
        $this->master = $this->DB()->database('master', true);
        $this->slave = $this->DB()->database('slave', true);
        $this->redis_master = $this->DB()->redis('master_test', 0);
        $this->redis_slave = $this->DB()->redis('slave_test', 0);
    }

    //开启事务
    function trans_begin()
    {
        $this->master->query('START TRANSACTION');
    }

    //事务回滚
    function trans_rollback()
    {
        $this->master->query('ROLLBACK');
    }

    //提交事务̬
    function trans_commit()
    {
        $this->master->query('COMMIT');
    }

    //数据库驱动连接
    protected function DB()
    {
        if ( empty(self::$db) ) {
            require_once($_ENV['COMPONENT_ROOT'].'/DAO/DAO.php');
            self::$db = new \DAO;
        }
        return self::$db;
    }
}