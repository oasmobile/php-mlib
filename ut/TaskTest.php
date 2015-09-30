<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-29
 * Time: 17:53
 */

namespace Oasis\Mlib\UnitTesting;

use Oasis\Mlib\Event\Event;
use Oasis\Mlib\Logging\Logger;
use Oasis\Mlib\Task\DelayTask;
use Oasis\Mlib\Task\DetachedTask;
use Oasis\Mlib\Task\LoopTask;
use Oasis\Mlib\Task\ParallelTask;
use Oasis\Mlib\Task\Runnable;
use Oasis\Mlib\Task\SequentialTask;
use Oasis\Mlib\Task\Task;
use Oasis\Mlib\Task\TimeboxedTask;

class TaskTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mock;
    protected $tmpfile;

    public static function setUpBeforeClass()
    {
        Logger::enableConsoleLog(false);
    }

    public static function tearDownAfterClass()
    {
        Logger::enableConsoleLog(true);
    }

    protected function setUp()
    {
        $this->tmpfile = tempnam(sys_get_temp_dir(), "tast-test");
        $this->mock    = $this->getMockBuilder("stdClass")
                              ->setMethods([
                                               'onStart',
                                               'onError',
                                               'onSuccess',
                                               'onComplete',
                                           ])
                              ->getMock();

    }

    protected function tearDown()
    {
        unlink($this->tmpfile);
    }

    public function testSimpleTask()
    {
        $task = new Task(function () {
            file_put_contents($this->tmpfile, "abc");
        });
        $task->addEventListener(Runnable::EVENT_START, [$this->mock, 'onStart']);
        $task->addEventListener(Runnable::EVENT_ERROR, [$this->mock, 'onError']);
        $task->addEventListener(Runnable::EVENT_SUCCESS, [$this->mock, 'onSuccess']);
        $task->addEventListener(Runnable::EVENT_COMPLETE, [$this->mock, 'onComplete']);

        $this->mock->expects($this->once())
                   ->method('onStart')
                   ->with($this->isInstanceOf(Event::class));
        $this->mock->expects($this->never())
                   ->method('onError')
                   ->with($this->isInstanceOf(Event::class));
        $this->mock->expects($this->once())
                   ->method('onSuccess')
                   ->with($this->isInstanceOf(Event::class));
        $this->mock->expects($this->once())
                   ->method('onComplete')
                   ->with($this->isInstanceOf(Event::class));
        $task->run();
    }

    public function testSimpleTaskFailed()
    {
        $task = new Task(function () {
            throw new \Exception("some error");
        });
        $task->addEventListener(Runnable::EVENT_ERROR,
            function (Event $e) {
                file_put_contents($this->tmpfile, "abc");

                $e->cancel();
            });
        $task->run();
        $this->assertEquals("abc", file_get_contents($this->tmpfile));
    }

    public function testDelayTask()
    {
        //$this->markTestSkipped();

        $start = microtime(true);
        $task  = new DelayTask(2);
        $task->addEventListener(Runnable::EVENT_COMPLETE,
            function () use ($start) {
                $end     = microtime(true);
                $elapsed = $end - $start;
                $this->assertEquals(2, $elapsed, 'delay task length', 0.1);
            });
        $task->run();

    }

    public function testTimeboxedTask()
    {
        //$this->markTestSkipped();

        $start      = microtime(true);
        $delay_task = new DelayTask(1);
        $task       = new TimeboxedTask($delay_task, 2);
        $task->addEventListener(Runnable::EVENT_COMPLETE,
            function () use ($start) {
                $end     = microtime(true);
                $elapsed = $end - $start;
                $this->assertEquals(2, $elapsed, 'delay task length', 0.1);
            });
        $task->run();
    }

    public function testLoopTask()
    {
        $mock_task = $this->getMockBuilder('stdClass')
                          ->setMethods(['small_runner'])
                          ->getMock();
        /** @noinspection PhpParamsInspection */
        $task = new LoopTask(new Task([$mock_task, 'small_runner']), 3);
        $mock_task->expects($this->exactly(3))
                  ->method('small_runner');

        $this->mock->expects($this->once())
                   ->method('onComplete');

        $task->addEventListener(Runnable::EVENT_COMPLETE, [$this->mock, 'onComplete']);
        $task->run();
    }

    public function testSequentialTask()
    {
        $fh    = fopen($this->tmpfile, 'w');
        $task1 = new Task(function () use ($fh) {
            fwrite($fh, 'a');
        });
        $task2 = new Task(function () use ($fh) {
            fwrite($fh, 'b');
        });
        $task3 = new Task(function () use ($fh) {
            fwrite($fh, 'c');
        });
        $task4 = new Task(function () use ($fh) {
            fwrite($fh, 'd');
        });
        $task5 = new Task(function () use ($fh) {
            fclose($fh);
        });

        $seq = new SequentialTask([
                                      $task1,
                                      $task2,
                                      $task3,
                                      $task4,
                                      $task5,
                                  ]);
        $seq->run();

        $this->assertEquals('abcd', file_get_contents($this->tmpfile));
    }

    public function testParallelTask()
    {
        $task1 = new DelayTask(2);
        $task2 = new DelayTask(1);
        $task3 = new DelayTask(3);

        $start = microtime(true);
        $para  = new ParallelTask([
                                      $task1,
                                      $task2,
                                      $task3,
                                  ]);
        $para->addEventListener(Runnable::EVENT_COMPLETE,
            function () use ($start) {
                $end     = microtime(true);
                $elapsed = $end - $start;
                $this->assertEquals(3, $elapsed, 'Parallel task execution time', 0.1);
            });
        $para->run();
    }

    public function testParallelTaskWithConcurrencyLimit()
    {
        $task1 = new DelayTask(2);
        $task2 = new DelayTask(1);
        $task3 = new DelayTask(3);
        $task4 = new DelayTask(1);

        $start = microtime(true);
        $para  = new ParallelTask([
                                      $task1,
                                      $task2,
                                      $task3,
                                      $task4,
                                  ],
                                  2);
        $para->addEventListener(Runnable::EVENT_COMPLETE,
            function () use ($start) {
                $end     = microtime(true);
                $elapsed = $end - $start;
                $this->assertEquals(4, $elapsed, 'Parallel task execution time', 0.1);
            });
        $para->run();
    }

    public function testDetachedTask()
    {
        $task = new Task(function () {
            sleep(2);
            file_put_contents($this->tmpfile, 'abc');
        });
        $detached = new DetachedTask($task);
        $detached->run();

        $this->assertEmpty(file_get_contents($this->tmpfile));
        sleep(3);
        $this->assertEquals('abc', file_get_contents($this->tmpfile));
    }
}
