<?php
namespace Cache\Core\Contracts\Filesystem;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/8/11
 * Time: 16:59
 */
interface Filesystem
{
    public function disk($name=null);
}