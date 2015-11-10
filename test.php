#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\DevTools\SimpleProfiler;

require_once "bootstrap.php";
set_exception_handler(
    function (Exception $e) {
        mtrace($e, "Exception caught in the end");
    }
);

$a = '';
for ($i = 0; $i < 30; ++$i) {
    SimpleProfiler::start();
    for ($j = 0; $j < 500; ++$j) {
        SimpleProfiler::start();
        usleep(10);
        SimpleProfiler::end();
    }
    SimpleProfiler::end();
}

var_dump(SimpleProfiler::getStats());
