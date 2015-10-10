#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\Event\Event;
use Oasis\Mlib\Task\BackgroundProcessRunner;
use Oasis\Mlib\Task\BackgroundTask;

require_once __DIR__ . "/vendor/autoload.php";

$q = new \Oasis\Mlib\AwsWrappers\SqsQueue([
                                              "profile" => "minhao",
                                              "region"  => "us-east-1",
                                              "version" => "latest",
                                          ],
                                          "test-queue");

try {
    $payroll = "\xffabc";
    $md5     = md5($payroll);
    mdebug("Sending payroll md5 = " . $md5);
    //$q->purge();
    $sent = $q->sendMessage(base64_encode($payroll),
                            0,
                            [
                                "中国" => [
                                    'DataType'    => 'String',
                                    'StringValue' => '就',
                                ],
                            ]);
    var_dump($sent);

    while ($msg = $q->receiveMessageWithAttributes(['中国'])) {
        var_dump($msg);

        $packed = unpack('H*', base64_decode($msg->getBody()));
        mdebug(mdump($packed));
        $q->deleteMessage($msg);
    }
} catch (Exception $e) {
    mtrace($e);
}


