<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 14:52
 */

namespace Oasis\Mlib\Task;

class SequentialTask extends AbstractTask
{
    /** @var Runnable[] */
    protected $origBacklog = [];

    /** @var Runnable[] */
    protected $backlog = [];
    protected $failed  = false;

    /**
     * @param Runnable[] $backlog
     */
    function __construct($backlog)
    {
        $this->origBacklog = [];
        foreach ($backlog as $task) {
            $task->addEventListener(self::EVENT_ERROR,
                function () {
                    $this->failed = true;
                    $this->dispatch(self::EVENT_ERROR);
                });
            $task->addEventListener(self::EVENT_COMPLETE,
                function () {
                    if ($this->isFailed()) {
                        mwarning("Sequential task failed");
                        $this->dispatch(self::EVENT_ERROR);
                        $this->dispatch(self::EVENT_COMPLETE);
                    }
                    else {
                        $this->runNext();
                    }
                });
            $this->origBacklog[] = $task;
        }
    }

    public function run()
    {
        $this->backlog = $this->origBacklog;
        $this->failed  = false;

        $this->dispatch(self::EVENT_START);

        $this->runNext();
    }

    protected function runNext()
    {
        if (count($this->backlog) == 0) {
            $this->dispatch(self::EVENT_SUCCESS);
            $this->dispatch(self::EVENT_COMPLETE);

            return;
        }

        /** @var AbstractTask $task */
        $task = array_shift($this->backlog);
        $task->run();
    }

    /**
     * @return boolean
     */
    public function isFailed()
    {
        return $this->failed;
    }
}
