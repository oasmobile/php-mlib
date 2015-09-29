<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 12:21
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Event\Event;

class Task extends AbstractTask
{
    protected $callback;
    protected $expected_result = null;
    protected $params          = [];
    protected $result;

    function __construct(callable $callback)
    {
        $argc = func_num_args();
        if ($argc > 1) {
            $argv = func_get_args();
            array_shift($argv); // shifting 1st arg, which is $callback
            $this->params = $argv;
        }

        $this->callback = $callback;
    }

    public function run()
    {
        $this->result = null;

        $this->dispatch(self::EVENT_START);

        try {
            $this->result = call_user_func_array($this->callback, $this->params);
            if ($this->expected_result === null
                || $this->expected_result === $this->result
            ) {
                $this->dispatch(self::EVENT_SUCCESS);
            }
            else {
                $this->dispatch(self::EVENT_ERROR);
            }
        } catch (\Exception $e) {
            $evt = new Event(self::EVENT_ERROR, $e);
            $this->dispatch($evt);
            if (!$evt->isCancelled()) {
                mtrace($e, "Exception not cancelled in Task execution.");
            }
        }

        $this->dispatch(self::EVENT_COMPLETE);
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return null
     */
    public function getExpectedResult()
    {
        return $this->expected_result;
    }

    /**
     * @param null $expected_result
     */
    public function setExpectedResult($expected_result)
    {
        $this->expected_result = $expected_result;
    }
}
