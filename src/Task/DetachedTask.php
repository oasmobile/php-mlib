<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-30
 * Time: 15:00
 */

namespace Oasis\Mlib\Task;

class DetachedTask extends AbstractTask
{
    protected $task;

    function __construct(Runnable $task)
    {
        $this->task = $task;
    }
    
    public function run()
    {
        $inner_bg = function () {
            $real_task = new BackgroundTask($this->task);
            $real_task->run();
            exit(0);
        };
        $outer_bg = new BackgroundTask(new Task($inner_bg));
        $outer_bg->run();
        BackgroundProcessRunner::wait();
    }
}
