#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\Task\ParallelTask;
use Oasis\Mlib\Task\Runnable;
use Oasis\Mlib\Task\Task;

require_once __DIR__ . "/vendor/autoload.php";

$task  = new Task(
    function () {
        throw new Exception("Some exception");
    }
);
$task2 = new Task(
    function () {
        throw new RuntimeException("Some runtime exception");
    }
);

$task->addEventListener(
    Runnable::EVENT_ERROR, function(){
    mdebug("task error");
});
$task2->addEventListener(
    Runnable::EVENT_ERROR, function(){
    mdebug("task2 error");
});

$paraTask = new ParallelTask(
    [
        "mytask" => $task,
        $task2,
    ]
);
$paraTask->run();
