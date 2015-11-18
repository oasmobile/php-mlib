#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

require_once "bootstrap.php";

$table = new \Oasis\Mlib\AwsWrappers\DynamoDbTable(
    [
        "profile" => "egg-user",
        "region"  => "us-east-1",
        "version" => "latest",
    ],
    "egg-user-task-info",
    [
        "uuid"   => "string",
        "taskid" => "number",
    ]
);
$c = $table->count(
    "#taskid = :taskid",
    ["#taskid" => "taskid"],
    [":taskid" => 7]
    ,"taskid-index"
);

mdebug($c);
