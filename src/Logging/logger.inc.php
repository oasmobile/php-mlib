<?php
/**
 * Created by PhpStorm.
 *
 * For backward compatibility with old mlib
 *
 * This file provides global functions to log messages in various log-levels
 *
 * To alter logpath, min-log-level, as well as other monolog related functionalities, please refer to:
 *      Oasis\Mlib\Logging\Logger
 *
 * User: minhao
 * Date: 2015-09-15
 * Time: 10:43
 */
use Oasis\Mlib\Logging\Logger;

function mdebug($msg, $context = [])
{
    Logger::debug($msg, $context);
}

function minfo($msg, $context = [])
{
    Logger::info($msg, $context);
}

function mwarning($msg, $context = [])
{
    Logger::warning($msg, $context);
}

function mnotice($msg, $context = [])
{
    Logger::notice($msg, $context);
}

function merror($msg, $context = [])
{
    Logger::error($msg, $context);
}

function mcritical($msg, $context = [])
{
    Logger::critical($msg, $context);
}

function malert($msg, $context = [])
{
    Logger::alert($msg, $context);
}

function memergency($msg, $context = [])
{
    Logger::emergency($msg, $context);
}

function mtrace(\Exception $e, $prompt_string = "", $logLevel = Logger::INFO)
{
    Logger::log($logLevel, $prompt_string . Logger::getExceptionDebugInfo($e));
}

function mdump($obj)
{
    return print_r($obj, true);
}
