#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */
use League\Flysystem\Filesystem;
use Oasis\Mlib\FlysystemWrappers\AppendableLocal;
use Oasis\Mlib\FlysystemWrappers\AppendableFilesystem;
use Oasis\Mlib\Resources\AbstractAwsS3Resource;
use Oasis\Mlib\Resources\AbstractLocalDataStorageResource;

require_once __DIR__ . "/vendor/autoload.php";

//class AwsS3Filesystem extends AbstractAwsS3Resource
//{
//    /**
//     * @return Filesystem
//     */
//    public static function getFilesystem()
//    {
//        return self::instance()->getResource("/tmp");
//    }
//
//    public function getConfig($key = '')
//    {
//        return [
//            "profile" => "adc-s3",
//            "region"  => "us-east-1",
//            "version" => "latest",
//            "bucket"  => "brotsoft-marketing-campaign-data",
//            "prefix"  => "test/",
//        ];
//    }
//}
//
//$finder = AwsS3Filesystem::instance()->finder();

class DataStorageFilesystem extends AbstractLocalDataStorageResource
{
    public static function getRealSystemPath($path)
    {

        /** @var AppendableLocal $adapter */
        $adapter = self::getFilesystem()->getAdapter();

        return $adapter->applyPathPrefix($path);
    }

    public static function getFilesystem()
    {
        /** @var AppendableFilesystem $fs */
        $fs = self::instance()->getResource();

        return $fs;
    }

    public function getConfig($key = '')
    {
        return "/tmp";
    }
}
$finder = DataStorageFilesystem::instance()->finder();

$finder->path("#sme_data/[0-9]+/[0-9]+#");

/** @var \Symfony\Component\Finder\SplFileInfo $splinfo */
foreach ($finder as $splinfo) {
    mdebug("spl: " . $splinfo);
    mdebug("path: " . $splinfo->getPathname());
    mdebug("relative: " . $splinfo->getRelativePathname());
}
