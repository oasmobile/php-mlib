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
        /** @noinspection PhpUnusedParameterInspection */
        $runner->addEventListener(
            BackgroundProcessRunner::EVENT_START,
            function (Event $e) {
                $this->isRunning = true;
                mdebug("Background task START.");
                $this->dispatch(self::EVENT_START);
            }
        );
        $runner->addEventListener(
            BackgroundProcessRunner::EVENT_EXIT,
            function (Event $e) {
                $return_code = $e->getContext();
                if ($return_code == 0 && !$this->isFailed) {
                    mdebug("Background task SUCCESS.");
                    $this->dispatch(self::EVENT_SUCCESS);
                }
                else {
                    mdebug("Background task ERROR.");
                    $this->dispatch(self::EVENT_ERROR);
                }
                $this->isRunning = false;
                mdebug("Background task COMPLETE.");
                $this->dispatch(self::EVENT_COMPLETE);
            }
        );
        $runner->start();

        return $runner->getChildPid();
    }

}
