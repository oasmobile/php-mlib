<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-15
 * Time: 10:43
 */
function mdebug($msg, $context = [])
{
    \Oasis\Mlib\Logging\Logger::debug($msg, $context);
}
