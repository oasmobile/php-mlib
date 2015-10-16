<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-16
 * Time: 18:30
 */

namespace Oasis\Mlib\UnitTesting;

use Oasis\Mlib\Cli\CommandLineArgParser;

class CommandLineArgParserTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_METHOD = "showErrorAndExit";

    /**
     * @var CommandLineArgParser
     */
    protected $parser;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mock;

    protected function setUp()
    {
        $this->parser = $this->getMockBuilder("Oasis\\Mlib\\Cli\\CommandLineArgParser")
                             ->setMethods(
                                 [
                                     self::ERROR_METHOD,
                                 ]
                             )
                             ->getMock();
        $this->mock   = $this->parser;
    }

    public function testGet()
    {
        $args = [
            "self",
            "-d",
            "-ejj",
        ];

        $this->parser->add('d')->cannotBeFollowed();
        $this->parser->add('e');
        $this->mock->expects($this->never())
                   ->method(self::ERROR_METHOD);
        $this->parser->run(count($args), $args);
        $this->assertSame(false, $this->parser->get('d'));
        $this->assertEquals('jj', $this->parser->get('e'));

    }

    public function testHas()
    {
        $args = [
            "self",
            "-d",
            "-ejj",
        ];

        $this->parser->add('d')->cannotBeFollowed();
        $this->parser->add('e');
        $this->parser->add('f');
        $this->mock->expects($this->never())
                   ->method(self::ERROR_METHOD);
        $this->parser->run(count($args), $args);
        $this->assertTrue($this->parser->has('d'));
        $this->assertTrue($this->parser->has('e'));
        $this->assertFalse($this->parser->has('f'));
    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testManatoryParameter()
    {
        $args = [
            "self",
            "-ddd",
        ];

        $this->parser->add('d')->isMandatory();

        $this->mock->expects($this->never())
                   ->method(self::ERROR_METHOD);

        $this->parser->run(count($args), $args);
        $this->assertTrue($this->parser->has('d'));
    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testManatoryParameterMissing()
    {
        $args = [
            "self",
            "-ddd",
        ];

        $this->parser->add('e')->isMandatory();

        $this->mock->expects($this->once())
                   ->method(self::ERROR_METHOD)
                   ->willThrowException(new \Exception());

        try {
            $this->parser->run(count($args), $args);
            $this->fail("Didn't throw");
        } catch (\Exception $e) {
        }
    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testParameterWithValue()
    {
        $args = [
            "self",
            "-ddd",
            "-e",
            "aaa",
        ];

        $this->parser->add('d');
        $this->parser->add('e');

        $this->parser->run(count($args), $args);

        $this->assertEquals('dd', $this->parser->get('d'));
        $this->assertEquals('aaa', $this->parser->get('e'));
    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testParameterWithRequiredValue()
    {
        $args = [
            "self",
            "-ddd",
        ];

        $this->parser->add('d')->requiresValue();
        $this->mock->expects($this->never())
                   ->method(self::ERROR_METHOD);
        $this->parser->run(count($args), $args);

        $this->assertEquals('dd', $this->parser->get('d'));
    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testParameterMissingRequiredValue()
    {
        $args = [
            "self",
            "-ddd",
            "-e",
        ];

        $this->parser->add('d')->requiresValue();
        $this->parser->add('e')->requiresValue();
        $this->mock->expects($this->once())
                   ->method(self::ERROR_METHOD)
                   ->willThrowException(new \Exception());
        try {
            $this->parser->run(count($args), $args);
            $this->fail("Exception not thrown when required value is missing");
        } catch (\Exception $e) {

        }

    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testParameterNotFollowable()
    {
        $args = [
            "self",
            "-ddd",
        ];

        $this->parser->add('d')->cannotBeFollowed();

        $this->mock->expects($this->once())
                   ->method(self::ERROR_METHOD)
                   ->willThrowException(new \Exception());
        try {
            $this->parser->run(count($args), $args);
            $this->fail("Exception not thrown when parameter cannot be followed has a value");
        } catch (\Exception $e) {

        }

    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testParameterWithDefaultValue()
    {
        $args = [
            "self",
            "-d",
            "-ekk",
        ];

        $this->parser->add('d')->hasDefaultValue('jj');
        $this->parser->add('e')->hasDefaultValue('mm');
        $this->mock->expects($this->never())
                   ->method(self::ERROR_METHOD);
        $this->parser->run(count($args), $args);
        $this->assertEquals('jj', $this->parser->get('d'));
        $this->assertEquals('kk', $this->parser->get('e'));
    }

    /**
     * @depends testHas
     * @depends testGet
     */
    public function testParameterWithAlias()
    {
        $args = [
            "self",
            "-d",
            "ekk",
            "--fire",
            "job",
        ];

        $this->parser->add('d')->aliasTo('e');
        $this->parser->add('f')->aliasTo('fire');
        $this->mock->expects($this->never())
                   ->method(self::ERROR_METHOD);
        $this->parser->run(count($args), $args);
        $this->assertEquals('ekk', $this->parser->get('d'));
        $this->assertEquals('ekk', $this->parser->get('e'));
        $this->assertEquals('job', $this->parser->get('f'));
        $this->assertEquals('job', $this->parser->get('fire'));
    }

}
