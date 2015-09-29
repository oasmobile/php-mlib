<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-28
 * Time: 11:50
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Event\EventDispatcherInterface;

interface Runnable extends EventDispatcherInterface
{
    const EVENT_START    = 'start';
    const EVENT_SUCCESS  = 'success';
    const EVENT_ERROR    = 'error';
    const EVENT_COMPLETE = 'complete';

    public function run();
}
