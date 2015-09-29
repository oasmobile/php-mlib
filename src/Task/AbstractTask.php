<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-28
 * Time: 16:55
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Event\EventDispatcherTrait;

abstract class AbstractTask implements Runnable
{
    use EventDispatcherTrait;
}
