<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 18:55
 */

namespace Oasis\Mlib\Resources;

use Oasis\Mlib\AwsWrappers\SqsQueue;

abstract class AbstractAwsSqsResource extends AbstractResourcePoolBase
{
    public function createResource($key = '')
    {
        $config = $this->getConfig($key);
        $queue  = new SqsQueue(
            $config,
            $config['queueName']
        );

        return $queue;
    }
}
