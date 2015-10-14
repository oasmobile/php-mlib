<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-28
 * Time: 14:25
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Event\Event;

class ParallelTask extends AbstractTask
{
    const EVENT_TASK_DEAD = "task_dead";

    const NO_LIMIT = -1;

    /** @var BackgroundTask[] */
    protected $origBacklog = [];
    /** @var int */
    protected $maxConcurrentTask;

    /** @var BackgroundTask[] */
    protected $backlog;
    /** @var BackgroundTask[] */
    protected $runningBackgroundTasks = [];
    protected $succeeded              = 0;
    protected $failed                 = 0;
    protected $totalExecuted          = 0;

    /**
     * @param Runnable[] $backlog
     * @param int        $maxConcurrentTask
     */
    function __construct($backlog, $maxConcurrentTask = self::NO_LIMIT)
    {
        if ($maxConcurrentTask != self::NO_LIMIT && $maxConcurrentTask < 1) {
            throw new \InvalidArgumentException("Num of max concurrent task for a ParallelTask should be at least 1");
        }

        $this->origBacklog = [];
        foreach ($backlog as $k => $runnable) {
            $bgTask = new BackgroundTask($runnable);
            $bgTask->addEventListener(
                Runnable::EVENT_START,
                function () {
                    $this->totalExecuted++;
                }
            );
            $bgTask->addEventListener(
                Runnable::EVENT_SUCCESS,
                function () {
                    $this->succeeded++;
                }
            );
            $bgTask->addEventListener(
                Runnable::EVENT_ERROR,
                function (Event $e) {
                    $key = array_search($e->getTarget(), $this->origBacklog, true);
                    $this->failed++;
                    mwarning(
                        "There is one bgTask (key = $key) failed for during parallel running. "
                        . "Total failed = {$this->failed}"
                    );
                    $this->dispatch(new Event(self::EVENT_TASK_DEAD, $key));
                }
            );
            $bgTask->addEventListener(
                Runnable::EVENT_COMPLETE,
                function (Event $e) {
                    $key = array_search($e->getTarget(), $this->runningBackgroundTasks, true);
                    if ($key === false) {
                        throw new \LogicException("Unable to find completed bg task from running list!");
                    }
                    array_splice($this->runningBackgroundTasks, $key, 1);
                    $this->startNext();
                }
            );
            $this->origBacklog[$k] = $bgTask;
        }
        $this->maxConcurrentTask = $maxConcurrentTask;
    }

    public function run()
    {
        if (count($this->runningBackgroundTasks) > 0) {
            throw new \LogicException("Cannot restart a parallel task which is already running!");
        }

        $this->backlog                = $this->origBacklog;
        $this->runningBackgroundTasks = [];
        $this->succeeded              = 0;
        $this->failed                 = 0;
        $this->totalExecuted          = 0;

        mdebug("ParallelTask START");
        $this->dispatch(self::EVENT_START);

        while ($this->startNext()) {
            ;
        }

        BackgroundProcessRunner::wait();

        return true;
    }

    public function startNext()
    {
        if (count($this->backlog) == 0) {
            if (count($this->runningBackgroundTasks) == 0) {
                $this->finish();
            }

            return false;
        }

        if ($this->maxConcurrentTask != self::NO_LIMIT
            && count($this->runningBackgroundTasks) >= $this->maxConcurrentTask
        ) {
            return false;
        }

        /** @var BackgroundTask $task */
        $task = array_shift($this->backlog);
        $task->run();
        $this->runningBackgroundTasks[] = $task;

        return true;
    }

    protected function finish()
    {
        mdebug(
            "Parallel task finished running, stats = {$this->succeeded}/{$this->failed}/{$this->totalExecuted} (S/F/T)"
        );

        if ($this->failed > 0) {
            mdebug("ParallelTask ERROR");
            $this->dispatch(self::EVENT_ERROR);
        }
        elseif ($this->succeeded > 0) {
            mdebug("ParallelTask SUCCESS");
            $this->dispatch(self::EVENT_SUCCESS);
        }
        mdebug("ParallelTask COMPLETE");
        $this->dispatch(self::EVENT_COMPLETE);
    }

    /**
     * @return int
     */
    public function getSucceeded()
    {
        return $this->succeeded;
    }

    /**
     * @return int
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * @return int
     */
    public function getTotalExecuted()
    {
        return $this->totalExecuted;
    }
}
