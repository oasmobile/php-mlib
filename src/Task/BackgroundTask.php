<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 14:31
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Event\Event;

class BackgroundTask extends AbstractTask
{
    protected $isRunning = false;
    protected $isFailed  = false;

    protected $task;

    function __construct(Runnable $task)
    {
        $this->task = $task;
        $this->task->addEventListener(
            Runnable::EVENT_ERROR,
            function () {
                $this->isFailed = true;
            }
        );
    }

    public function run()
    {
        if ($this->isRunning) {
            throw new \LogicException("Cannot restart a background task which is already running!");
        }
        $this->isFailed = false;

        $runner = new BackgroundProcessRunner($this->task);
        $runner->addEventListener(
            BackgroundProcessRunner::EVENT_START,
            function (Event $e) {
                $this->isRunning = true;
                $this->dispatch(self::EVENT_START);
            }
        );
        $runner->addEventListener(
            BackgroundProcessRunner::EVENT_EXIT,
            function (Event $e) {
                $return_code = $e->getContext();
                if ($return_code == 0 && !$this->isFailed) {
                    $this->dispatch(self::EVENT_SUCCESS);
                }
                else {
                    $this->dispatch(self::EVENT_ERROR);
                }
                $this->isRunning = false;
                $this->dispatch(self::EVENT_COMPLETE);
            }
        );
        $runner->start();

        return $runner->getChildPid();
    }

}
