#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\AwsWrappers\DynamoDbTable;

require_once "bootstrap.php";
ini_set('xdebug.var_display_max_depth', 5);

$table  = new DynamoDbTable(
    [
        "profile" => "minhao",
        "region"  => "us-west-2",
    ],
    "mdata_users"
);
$result = $table->getConsumedCapacity(DynamoDbTable::PRIMARY_INDEX,
    1800,
    24);
var_dump($result);

//$result = $table->getThroughput("taskid-index");
//var_dump($result);
//$table->setThroughput(50, 50, "taskid-index");
//$result = $table->getThroughput("taskid-index");
//var_dump($result);
