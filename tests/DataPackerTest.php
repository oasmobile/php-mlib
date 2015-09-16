<?php
use League\Flysystem\Adapter\Local;
use Oasis\Mlib\Data\DataPacker;

/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 17:21
 */
class DataPackerTest extends PHPUnit_Framework_TestCase
{
    protected $tmpfile;

    protected function setUp()
    {
        $this->tmpfile = tempnam(sys_get_temp_dir(), "data-packer-test");
    }

    protected function tearDown()
    {
        unlink($this->tmpfile);
    }

    public function testPackingAndUnpacking()
    {
        $obj = new Local(dirname($this->tmpfile)); // just some fancy object

        $packer = new DataPacker();
        $data   = $packer->pack($obj);
        $this->assertTrue(is_string($data));
        $this->assertGreaterThan(4, strlen($data));

        $unpacked = $packer->unpack($data);
        $this->assertInstanceOf(Local::class, $unpacked);
    }

    public function testStreamOperation()
    {
        $obj = new Local(dirname($this->tmpfile)); // just some fancy object

        $packer = new DataPacker();
        $fh     = fopen($this->tmpfile, 'w');
        $packer->packToStream($fh, $obj);
        $packer->packToStream($fh, $obj);
        $packer->packToStream($fh, $obj);
        fclose($fh);

        $fh    = fopen($this->tmpfile, 'r');
        $count = 0;
        while ($obj = $packer->unpackFromStream($fh)) {
            $this->assertInstanceOf(Local::class, $obj);
            $count++;
        }
        $this->assertEquals($count, 3);
    }

    public function testUsingSystemSerializer()
    {
        $obj = new Local(dirname($this->tmpfile)); // just some fancy object

        $packer = new DataPacker("serialize", "unserialize");
        $data   = $packer->pack($obj);
        $this->assertTrue(is_string($data));
        $this->assertGreaterThan(4, strlen($data));

        $unpacked = $packer->unpack($data);
        $this->assertInstanceOf(Local::class, $unpacked);
    }
}
