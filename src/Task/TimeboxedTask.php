<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 16:31
 */

namespace Oasis\Mlib\Task;

class TimeboxedTask extends AbstractTask
{
    protected $task;
    protected $size;

    protected $taskFailed = false;
    protected $start_time = 0;

    function __construct(Runnable $task, $timebox_size_in_seconds)
    {
        $this->task = $task;
        $this->size = $timebox_size_in_seconds;
        $this->task->addEventListener(Runnable::EVENT_ERROR,
            function () {
                $this->taskFailed = true;
            });
        $this->task->addEventListener(Runnable::EVENT_COMPLETE,
            function () {
                $finish  = time();
                $elapsed = $finish - $this->start_time;
                if ($elapsed < $this->size) {
                    $delay = new DelayTask($this->size - $elapsed);
                    $delay->addEventListener(Runnable::EVENT_COMPLETE,
                        function () {
                            if ($this->taskFailed) {
                                $this->dispatch(self::EVENT_ERROR);
                            }
                            else {
                                $this->dispatch(self::EVENT_SUCCESS);
                            }

                            $this->dispatch(self::EVENT_COMPLETE);
                        });
                    $delay->run();
                }
            });
    }

    public function run()
    {
        $this->start_time = time();
        $this->taskFailed = false;

        $this->task->run();
    }
}
