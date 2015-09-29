#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\Task\LoopTask;
use Oasis\Mlib\Task\ParallelTask;
use Oasis\Mlib\Task\SequentialTask;
use Oasis\Mlib\Task\Task;

require_once __DIR__ . "/vendor/autoload.php";

$tasks = [];
for ($i = 0; $i < 10; ++$i) {
    $task = new Task(function () use ($i) {
        mdebug("Task #$i running");
        //sleep($i);
        if ($i % 3 == 0) {
            //throw new \Exception("Failed #$i");
        }
    });
    $task->addEventListener(Task::EVENT_START,
        function () use ($i) {
            mdebug("Task #$i started");
        });
    $task->addEventListener(Task::EVENT_SUCCESS,
        function () use ($i) {
            mdebug("Task #$i ok");
        });
    $task->addEventListener(Task::EVENT_ERROR,
        function () use ($i) {
            mdebug("Task #$i error");
        });
    $task->addEventListener(Task::EVENT_COMPLETE,
        function () use ($i) {
            mdebug("Task #$i completed");
        });
    $tasks[] = $task;
}

$para = new ParallelTask([
                             new SequentialTask([
                                 $tasks[0], $tasks[1], $tasks[2],
                                                ]),
                             new SequentialTask([
                                 $tasks[3], $tasks[4], $tasks[5],
                                                ]),
                             new SequentialTask([
                                 $tasks[6], $tasks[7], $tasks[8],
                                                ]),
    ]);

$loop = new LoopTask(
    $para
//new TimeboxedTask($para, 2)
);
$loop->run();
