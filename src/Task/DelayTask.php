<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 15:54
 */

namespace Oasis\Mlib\Task;

class DelayTask extends AbstractTask
{
    protected $delay_in_seconds;
    protected $since_time;

    function __construct($delay_in_seconds, $since_time = 0)
    {
        $this->delay_in_seconds = $delay_in_seconds;
    }

    public function run()
    {
        $this->dispatch(self::EVENT_START);
        if ($this->delay_in_seconds > 0) {
            $now   = time();
            $from  = $this->since_time ? $this->since_time : $now;
            $until = $from + $this->delay_in_seconds;
            if ($until > $now) {
                sleep($until - $now);
            }
        }
        $this->dispatch(self::EVENT_SUCCESS);
        $this->dispatch(self::EVENT_COMPLETE);
    }
}
