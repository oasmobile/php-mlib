<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-11-09
 * Time: 13:46
 */
use Oasis\Mlib\Cli\MemoryMonitor;

require_once 'vendor/autoload.php';
MemoryMonitor::registerMonitorForTick();
