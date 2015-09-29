<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 16:21
 */

namespace Oasis\Mlib\Task;

class LoopTask extends AbstractTask
{
    const ENDLESS = -1;

    /** @var Runnable */
    protected $task;
    protected $count;
    protected $stop_on_error = true;

    protected $num_looped = 0;
    protected $succeeded  = 0;
    protected $failed     = 0;

    function __construct(Runnable $task, $count = self::ENDLESS, $stop_on_error = true)
    {
        $this->task          = $task;
        $this->count         = $count;
        $this->stop_on_error = $stop_on_error;

        $this->task->addEventListener(Runnable::EVENT_SUCCESS,
            function () {
                $this->succeeded++;
            });
        $this->task->addEventListener(Runnable::EVENT_ERROR,
            function () {
                $this->failed++;
            });
        $this->task->addEventListener(Runnable::EVENT_COMPLETE,
            function () {
                $this->num_looped++;
                mdebug("loop = {$this->num_looped}");
            });
    }
    
    public function run()
    {
        $this->succeeded  = 0;
        $this->failed     = 0;
        $this->num_looped = 0;

        do {
            mdebug("Looping task running, num_looped = " . $this->num_looped);
            $this->task->run();
            if ($this->failed > 0 && $this->stop_on_error) {
                break;
            }
        } while ($this->count == self::ENDLESS || $this->num_looped < $this->count);

        if ($this->failed > 0) {
            $this->dispatch(self::EVENT_ERROR);
        }
        elseif ($this->succeeded > 0) {
            $this->dispatch(self::EVENT_SUCCESS);
        }

        $this->dispatch(self::EVENT_COMPLETE);

        return;
    }
}
