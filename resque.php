<?php
if (!extension_loaded('AMQP')) {
    die('please set php.ini about php_amqp extension!');
}

return require_once(__DIR__ . '/Core/Main.php');