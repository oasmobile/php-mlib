<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-14
 * Time: 17:30
 */
namespace Oasis\Mlib\UnitTesting;

use Aws\Command;
use Aws\S3\Exception\S3Exception;
use League\Flysystem\Filesystem;
use Oasis\Mlib\FlysystemWrappers\FixedAwsS3Adapter;
use PHPUnit_Framework_TestCase;

class AwsS3FilesystemTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testFixedFileRead()
    {
        $client = $this->getMockBuilder("Aws\\S3\\S3Client")
                       ->setMethods([
                                        "execute",
                                        "doesObjectExist",
                                        "getCommand",
                                    ])
                       ->disableOriginalConstructor()
                       ->getMock();

        $client->expects($this->once())
               ->method("doesObjectExist")
               ->with($this->equalTo("bucket-name"),
                      $this->equalTo("prefix/path"))
               ->willReturn(true);

        $client->expects($this->any())
               ->method("getCommand")
               ->withAnyParameters()
               ->willReturn($command = new Command('dummy'));

        $client->expects($this->once())
               ->method("execute")
               ->withAnyParameters()
               ->willThrowException(new S3Exception("expected error", $command));

        /** @noinspection PhpParamsInspection */
        $adapter = new FixedAwsS3Adapter($client, "bucket-name", "prefix");
        $fs      = new Filesystem($adapter);
        $fs->read('path');
    }
}
