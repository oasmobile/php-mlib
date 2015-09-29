<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 10:57
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Event\Event;
use Oasis\Mlib\Event\EventDispatcherInterface;
use Oasis\Mlib\Event\EventDispatcherTrait;

class BackgroundProcessRunner implements EventDispatcherInterface
{
    use EventDispatcherTrait;

    const EVENT_START = "start";
    const EVENT_EXIT  = "exit";

    /** @var BackgroundProcessRunner[] */
    static protected $running_runners = [];

    /** @var Runnable */
    protected $runnable;
    /** @var int child process id */
    protected $child_pid;

    function __construct(Runnable $runnable)
    {
        $this->runnable = $runnable;
        $this->addEventListener(self::EVENT_START, [static::class, 'onRunnerStarted'], PHP_INT_MAX);
    }

    /**
     * Wait for objects of BackgroundProcessRunner to run and exit
     *
     * @param int  $waitpid                         < -1   wait for any child process whose process group ID is equal
     *                                              to the absolute value of pid.
     *                                              -1     wait for any child process; this is the same behaviour that
     *                                              the wait function exhibits.
     *                                              0      wait for any child process whose process group ID is equal
     *                                              to that of the calling process.
     *                                              > 0    wait for the child whose process ID is equal to the value of
     *                                              pid.
     * @param bool $hangs                           whether the calling function should hang until all managed children
     *                                              exit
     * @param int  $check_interval_in_millisenconds interval in milliseconds, between each wait try
     */
    public static function wait($waitpid = -1, $hangs = true, $check_interval_in_millisenconds = 100)
    {
        while (true) {
            $status = 0;
            $pid    = pcntl_waitpid($waitpid, $status, WNOHANG);

            if ($pid == 0) { // no child process has quit
                continue;
            }
            else if ($pid > 0) { // child process with pid = $pid exits
                $return_code = pcntl_wexitstatus($status);
                if (array_key_exists($pid, self::$running_runners)) { // find runner
                    $runner = self::$running_runners[$pid];
                    unset(self::$running_runners[$pid]);
                    mdebug("Child process #$pid finished running, returns $return_code");
                    $runner->dispatch(new Event(self::EVENT_EXIT, $return_code));
                }
                else {
                    mwarning("Process #$pid is not handled by <" . get_called_class() . ">");
                    continue;
                }
            }
            else { // error
                $errno = pcntl_get_last_error();
                if ($errno == PCNTL_ECHILD) {
                    // all children finished
                    mdebug("No more BackgroundProcessRunner children, continue ...");
                    break;
                }
                else {
                    // some other error
                    throw new \RuntimeException("Error waiting for process, error = " . pcntl_strerror($errno));
                }
            }

            if (!$hangs) {
                break;
            }

            usleep($check_interval_in_millisenconds * 1000);
        }
    }

    protected static function onRunnerStarted(Event $e)
    {
        /** @var static $runner */
        $runner                                    = $e->getTarget();
        self::$running_runners[$runner->child_pid] = $runner;

        mdebug("Runner started child in process #" . $runner->child_pid);
    }

    /**
     * @return int
     */
    public function getChildPid()
    {
        return $this->child_pid;
    }

    public function start()
    {
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("Error creating forked process, reason = "
                                        . pcntl_strerror(pcntl_get_last_error()));
        }
        elseif ($pid == 0) {
            try {
                $this->runnable->addEventListener(Runnable::EVENT_ERROR,
                    function () {
                        merror("Background task dispatches ERROR event.");
                        exit(-1);
                    });
                $this->runnable->run();
                exit(0);
            } catch (\Exception $e) {
                mtrace($e, "Runnable throws uncaught exception.");
                exit(-1);
            }
            // should not come here
            /** @noinspection PhpUnreachableStatementInspection */
            exit(-2);
        }
        else {
            $this->child_pid = $pid;
            $this->dispatch(new Event(self::EVENT_START));
        }

        return $pid;
    }
}
