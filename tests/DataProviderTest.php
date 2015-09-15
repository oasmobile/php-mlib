<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-15
 * Time: 19:04
 */
namespace Oasis\Mlib\UnitTesting;

use Oasis\Mlib\Data\ArrayDataProvider;
use Oasis\Mlib\Data\MandatoryValueMissingException;
use PHPUnit_Framework_TestCase;

class DataProviderTest extends PHPUnit_Framework_TestCase
{
    /** @var ArrayDataProvider */
    protected $dp = null;

    protected function setUp()
    {
        $data     = [
            "int"          => 1,
            "float"        => 2.4,
            "string"       => "name",
            "array"        => [
                0,
                1,
                2,
            ],
            "object"       => new \stdClass(),
            "bool"         => true,
            "bool_str_on"  => "on",
            "bool_str_off" => "off",
        ];
        $this->dp = new ArrayDataProvider($data);
    }

    public function testGet()
    {
        $this->assertEquals(1, $this->dp->getMandatory("int", ArrayDataProvider::INT_TYPE));
        $this->assertEquals(2, $this->dp->getMandatory("float", ArrayDataProvider::INT_TYPE));
        $this->assertEquals(0, $this->dp->getMandatory("string", ArrayDataProvider::INT_TYPE));
        $this->assertEquals(1, $this->dp->getMandatory("bool", ArrayDataProvider::INT_TYPE));
        $this->assertEquals(0, $this->dp->getMandatory("bool_str_on", ArrayDataProvider::INT_TYPE));

        $this->assertEquals(1, $this->dp->getMandatory("int", ArrayDataProvider::FLOAT_TYPE));
        $this->assertEquals(2.4, $this->dp->getMandatory("float", ArrayDataProvider::FLOAT_TYPE));
        $this->assertEquals(0, $this->dp->getMandatory("string", ArrayDataProvider::FLOAT_TYPE));
    }

    public function testMandatoryOk()
    {
        $this->dp->getMandatory("int");
    }

    public function testMandatoryNotExist()
    {
        $this->setExpectedException(MandatoryValueMissingException::class);
        $this->dp->getMandatory("java");
    }

    public function testOptionalNotExist()
    {
        $val = $this->dp->getOptional("java", ArrayDataProvider::STRING_TYPE, "bean");
        $this->assertEquals($val, "bean");
    }

    public function testOptionalExist()
    {
        $this->assertEquals(true, $this->dp->getOptional("bool", ArrayDataProvider::BOOL_TYPE, false));
    }
}
