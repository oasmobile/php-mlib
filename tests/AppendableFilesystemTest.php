<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-14
 * Time: 17:30
 */
namespace Oasis\Mlib\UnitTesting;

use Oasis\Mlib\FlysystemWrappers\AppendableFilesystem;
use Oasis\Mlib\FlysystemWrappers\AppendableLocal;
use PHPUnit_Framework_TestCase;

class AppendableFilesystemTest extends PHPUnit_Framework_TestCase
{
    public $tempdir = '';

    protected function setUp()
    {
        $this->tempdir = sys_get_temp_dir() . "/mlib-test/logs";
    }

    protected function tearDown()
    {
        $symfony_fs = new \Symfony\Component\Filesystem\Filesystem();
        if ($symfony_fs->exists($this->tempdir)) {
            $symfony_fs->remove($this->tempdir);
        }
    }

    public function testAppendableFilesystemCreation()
    {
        $adapter = new AppendableLocal($this->tempdir);
        $fs      = new AppendableFilesystem($adapter);

        return $fs;
    }

    /**
     * @depends testAppendableFilesystemCreation
     *
     * @param AppendableFilesystem $fs
     *
     * @return AppendableFilesystem
     */
    public function testAppendStreamOnNewFile(AppendableFilesystem $fs)
    {
        $newFile = "new_file.txt";

        $str = <<<STRING
A quick brown fox jumps over a lazy dog.
STRING;

        $fh = $fs->appendStream($newFile);
        fwrite($fh, $str);
        fclose($fh);

        $this->assertTrue($fs->has($newFile));
        $this->assertEquals($str, $fs->read($newFile));

        return $fs;
    }

    /**
     * @depends testAppendStreamOnNewFile
     *
     * @param AppendableFilesystem $fs
     *
     * @return AppendableFilesystem
     */
    public function testAppendStreamOnExistingFile(AppendableFilesystem $fs)
    {
        $newFile = "new_file.txt";

        $str = <<<STRING
abcdefghijklmn
STRING;

        $fh = $fs->appendStream($newFile);
        fwrite($fh, $str);
        fclose($fh);

        $this->assertContains($str, $fs->read($newFile));

        return $fs;
    }
}
