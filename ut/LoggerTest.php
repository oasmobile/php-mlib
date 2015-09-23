<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-10
 * Time: 20:53
 */
namespace Oasis\Mlib\UnitTesting;

use Oasis\Mlib\Logging\Logger;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected static $fs = null;

    protected $log_dir = '';

    public static function setUpBeforeClass()
    {
        self::$fs = new Filesystem();

    }

    protected function setUp()
    {
        $this->log_dir = sys_get_temp_dir() . "/mlib-test/logs";
        if (self::$fs->exists($this->log_dir)) {
            self::$fs->remove($this->log_dir);
        }
        Logger::init($this->log_dir);
        Logger::enableConsoleLog(false);
    }

    protected function tearDown()
    {
        if (self::$fs->exists($this->log_dir)) {
            self::$fs->remove($this->log_dir);
        }
    }

    public function testLogToFile()
    {
        $msg = "This is a debug message";
        Logger::debug($msg);

        $finder = new Finder();
        $finder->in($this->log_dir)
               ->path("/.log$/");

        $this->assertEquals(count($finder), 1);

        /** @var \Symfony\Component\Finder\SplFileInfo $path */
        foreach ($finder as $path) {
            $log_content = file_get_contents($path);
            $this->assertContains($msg, $log_content);
            break;
        }
    }

    public function testLogLevel()
    {
        $msg    = "This is a debug message";
        $errmsg = "This is an error message";
        Logger::setMinLogLevel(Logger::ERROR);
        Logger::debug($msg);
        Logger::error($errmsg);

        $finder = new Finder();
        $finder->in($this->log_dir)
               ->path("/.log$/");

        $this->assertEquals(count($finder), 1);

        /** @var \Symfony\Component\Finder\SplFileInfo $path */
        foreach ($finder as $path) {
            $log_content = file_get_contents($path);
            $this->assertNotContains($msg, $log_content);
            $this->assertContains($errmsg, $log_content);
            break;
        }
    }

    public function testErrorLog()
    {
        $msg    = "This is a debug message";
        $errmsg = "This is an error message";
        Logger::debug($msg);
        Logger::error($errmsg);

        $finder = new Finder();
        $finder->in($this->log_dir)
               ->path("/.error$/");

        $this->assertEquals(count($finder), 1);

        /** @var \Symfony\Component\Finder\SplFileInfo $path */
        foreach ($finder as $path) {
            $log_content = file_get_contents($path);
            $this->assertNotContains($msg, $log_content);
            $this->assertContains($errmsg, $log_content);
            break;
        }
    }

    public function testErrorLogNotTriggerred()
    {
        $msg = "This is a debug message";
        Logger::debug($msg);

        $finder = new Finder();
        $finder->in($this->log_dir)
               ->path("/.error$/");

        $this->assertEquals(count($finder), 1);

        /** @var \Symfony\Component\Finder\SplFileInfo $path */
        foreach ($finder as $path) {
            $log_content = file_get_contents($path);
            $this->assertNotContains($msg, $log_content);
            break;
        }
    }
}
