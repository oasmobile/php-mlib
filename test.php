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
$db->setCasField("completed_at");

//$obj = [
//    "uuid"         => 123,
//    "taskid"       => 11,
//    "version"      => 1,
//    "completed_at" => time(),
//    "name"         => null,
//    "is_student"   => true,
//];
//$db->set($obj);

$obj = $db->get(['uuid' => 123, 'taskid' => 11]);
$obj['name'] = "jason";
$obj['completed_at'] = time();
$db->set($obj, true);
