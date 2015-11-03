#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\AwsWrappers\DynamoDbItem;
use Oasis\Mlib\AwsWrappers\DynamoDbTable;

require_once __DIR__ . "/vendor/autoload.php";

set_exception_handler(function (Exception $e) {
    mtrace($e, "Exception caught in the end");
});

$config = [
    "profile" => "minhao",
    "region"  => "us-east-1",
    "version" => "latest",
];
$table  = "egg-user-task-info";
$types  = [
    "uuid"         => DynamoDbItem::ATTRIBUTE_TYPE_STRING,
    "taskid"       => DynamoDbItem::ATTRIBUTE_TYPE_NUMBER,
    "completed_at" => DynamoDbItem::ATTRIBUTE_TYPE_NUMBER,
];

$db = new DynamoDbTable($config, $table, $types);

//$db->set(
//    [
//        "uuid"      => "123",
//        "taskid"    => 2,
//        "orig_uuid" => "jack",
//    ]
//);
//$db->set(
//    [
//        "uuid"      => "124",
//        "taskid"    => 4,
//        "orig_uuid" => "rose",
//    ]
//);

$db->scanAndRun(
    function (array $item) {
        var_dump($item);
    }
);
