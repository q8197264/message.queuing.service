<?php
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/7/13
 * Time: 16:23
 *
 */
 Router::get('get/{method}/{any}', function($method,$any){
  return "get/".$method;
 });