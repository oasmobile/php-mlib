<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-10
 * Time: 14:24
 */

namespace Oasis\Mlib\AwsWrappers;

use Aws\Sqs\SqsClient;

class SqsQueue
{
    /** @var SqsClient */
    protected $client;
    protected $config;
    protected $url = null;
    protected $name;

    function __construct($aws_config, $name)
    {
        $this->client = new SqsClient($aws_config);
        $this->config = $aws_config;
        $this->name   = $name;
    }

    public function sendMessage($payroll, $delay = 0, $attributes = [])
    {
        $args = [
            "QueueUrl"    => $this->getQueueUrl(),
            "MessageBody" => $payroll,
        ];
        if ($delay) {
            $args['DelaySeconds'] = $delay;
        }
        if ($attributes) {
            $args['MessageAttributes'] = $attributes;
        }
        $result   = $this->client->sendMessage($args);
        $sent_msg = new SqsSentMessage($result->toArray());
        $md5      = md5($payroll);
        if ($result['MD5OfMessageBody'] != $md5) {
            throw new \RuntimeException("MD5 of payroll is different on sent message!");
        }

        return $sent_msg;
    }

    /**
     * @param int   $wait
     * @param int   $visibility_timeout
     * @param array $metas
     * @param array $message_attributes
     *
     * @return SqsReceivedMessage
     */
    public function receiveMessage($wait = null, $visibility_timeout = null, $metas = [], $message_attributes = [])
    {
        $ret = $this->receiveMessages(1, $wait, $visibility_timeout, $metas, $message_attributes);
        if (!$ret) {
            return null;
        }
        else {
            return $ret[0];
        }
    }

    /**
     * @param int   $max_count
     * @param int   $wait
     * @param int   $visibility_timeout
     * @param array $metas
     * @param array $message_attributes
     *
     * @return SqsReceivedMessage[]
     */
    public function receiveMessages($max_count = 1,
                                    $wait = null,
                                    $visibility_timeout = null,
                                    $metas = [],
                                    $message_attributes = [])
    {
        if ($max_count > 10 || $max_count < 1) {
            throw new \InvalidArgumentException("Max count for SQS message receiving is 10");
        }

        $args = [
            "QueueUrl"            => $this->getQueueUrl(),
            "MaxNumberOfMessages" => $max_count,
        ];
        if ($wait !== null && is_int($wait)) {
            $args['WaitTimeSeconds'] = $wait;
        }
        if ($visibility_timeout !== null && is_int($visibility_timeout)) {
            $args['VisibilityTimeout'] = $visibility_timeout;
        }
        if ($metas && is_array($metas)) {
            $args['AttributeNames'] = $metas;
        }
        if ($message_attributes && is_array($message_attributes)) {
            $args['MessageAttributeNames'] = $message_attributes;
        }

        $result   = $this->client->receiveMessage($args);
        $messages = $result['Messages'];
        if (!$messages) {
            return [];
        }

        $ret = [];
        foreach ($messages as $data) {
            $msg   = new SqsReceivedMessage($data);
            $ret[] = $msg;
        }

        return $ret;
    }

    /**
     * @param array $expected_message_attributes
     * @param int   $wait
     * @param int   $visibility_timeout
     * @param array $metas
     *
     * @return SqsReceivedMessage
     */
    public function receiveMessageWithAttributes(array $expected_message_attributes,
                                                 $wait = null,
                                                 $visibility_timeout = null,
                                                 $metas = [])
    {
        return $this->receiveMessage($wait, $visibility_timeout, $metas, $expected_message_attributes);
    }

    public function purge()
    {
        $this->client->purgeQueue(
            [
                "QueueUrl" => $this->getQueueUrl(),
            ]
        );
    }

    public function deleteMessage(SqsReceivedMessage $msg)
    {
        $this->client->deleteMessage(
            [
                "QueueUrl"      => $this->getQueueUrl(),
                "ReceiptHandle" => $msg->getReceiptHandle(),
            ]
        );
    }

    public function deleteMessages($messages)
    {
        $buffer = [];
        /** @var SqsReceivedMessage $msg */
        foreach ($messages as $msg) {
            $buffer[] = $msg;
            if (count($buffer) >= 10) {

                $entries = [];
                /** @var SqsReceivedMessage $bmsg */
                foreach ($buffer as $idx => $bmsg) {
                    $entries[] = [
                        "Id"            => "buf_$idx",
                        "ReceiptHandle" => $msg->getReceiptHandle(),
                    ];
                }
                $this->client->deleteMessageBatch(
                    [
                        "QueueUrl" => $this->getQueueUrl(),
                        "Entries"  => $entries,
                    ]
                );
                $buffer = [];
            }
        }
        if ($buffer) {
            $entries = [];
            /** @var SqsReceivedMessage $bmsg */
            foreach ($buffer as $idx => $bmsg) {
                $entries[] = [
                    "Id"            => "buf_$idx",
                    "ReceiptHandle" => $msg->getReceiptHandle(),
                ];
            }
            $this->client->deleteMessageBatch(
                [
                    "QueueUrl" => $this->getQueueUrl(),
                    "Entries"  => $entries,
                ]
            );
        }
    }

    public function getQueueUrl()
    {
        if (!$this->url) {
            $result = $this->client->getQueueUrl(
                [
                    "QueueName" => $this->name,
                ]
            );
            if ($result['QueueUrl']) {
                $this->url = $result['QueueUrl'];
            }
            else {
                throw new \RuntimeException("Cannot find queue url for queue named {$this->name}");
            }
        }

        return $this->url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
