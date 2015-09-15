<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-10
 * Time: 20:53
 */
namespace Oasis\Mlib\UnitTesting;

use Oasis\Mlib\MUtils;
use PHPUnit_Framework_TestCase;

class MUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testStringStartsWith()
    {
        $this->assertTrue(MUtils::stringStartsWith("abcdef", "ab"));
        $this->assertFalse(MUtils::stringStartsWith("abcdef", "cd"));
        $this->assertFalse(MUtils::stringStartsWith("abcdef", "ef"));
        $this->assertTrue(MUtils::stringStartsWith("abcdef", ""));
        $this->assertFalse(MUtils::stringStartsWith("", "abcdef"));
    }

    public function testStringEndsWith()
    {
        $this->assertFalse(MUtils::stringEndsWith("abcdef", "ab"));
        $this->assertFalse(MUtils::stringEndsWith("abcdef", "cd"));
        $this->assertTrue(MUtils::stringEndsWith("abcdef", "ef"));
        $this->assertTrue(MUtils::stringEndsWith("abcdef", ""));
        $this->assertFalse(MUtils::stringEndsWith("", "abcdef"));
    }

    public function testStringChopdown()
    {
        $str = "abcdefg";
        $this->assertEquals("abcd", MUtils::stringChopdown($str, 4));

        $chinese = "中国人";
        $this->assertEquals("中", MUtils::stringChopdown($chinese, 1));
    }
}
