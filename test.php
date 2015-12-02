#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

require_once "bootstrap.php";
ini_set('xdebug.var_display_max_depth', 5);

$table  = new \Oasis\Mlib\AwsWrappers\DynamoDbTable(
    [
        "profile" => "minhao",
        "region"  => "us-east-1",
    ],
    "egg-user-task-info"
);
$result = $table->getConsumedCapacity("taskid-index");
var_dump($result);
$result = $table->getThroughput("taskid-index");
var_dump($result);
$table->setThroughput(50, 50, "taskid-index");
$result = $table->getThroughput("taskid-index");
var_dump($result);
