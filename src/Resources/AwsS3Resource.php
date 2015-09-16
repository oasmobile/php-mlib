<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 18:55
 */

namespace Oasis\Mlib\Resources;

use Aws\S3\S3Client;
use Aws\S3\StreamWrapper;
use Aws\Sts\StsClient;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Plugin\ListPaths;

abstract class AwsS3Resource
{
    public static function getFilesystem()
    {
        /** @var Filesystem[] $instances */
        static $instances = [];
        if (!$instances[static::class] instanceof Filesystem) {
            $adapter                  = new AwsS3Adapter(
                self::getS3Client(),
                static::getS3Configuration()['bucket']
            );
            $instances[static::class] = new Filesystem($adapter);
            $instances[static::class]->addPlugin(new ListPaths());
        }

        return $instances[static::class];
    }

    public static function registerStreamWrapper($protocol = "s3")
    {
        static $regs = [];
        if (!$regs[static::class]) {
            StreamWrapper::register(self::getS3Client(), $protocol);
            $regs[static::class] = true;
        }

        return $regs[static::class];
    }

    public static function getS3Client()
    {
        /** @var S3Client[] $clients */
        static $clients = [];
        if (!$clients[static::class] instanceof S3Client) {
            $clients[static::class] = new S3Client(static::getS3Configuration());
        }

        return $clients[static::class];
    }

    public static function getTemporaryCredentials()
    {
        static $credentials = [];

        $now         = Carbon::now();
        $minExpireAt = $now->addHour(3);
        if (!$credentials[static::class]
            || !$credentials[static::class]['expireAt']
            || $credentials[static::class]['expireAt'] < $minExpireAt->getTimestamp()
        ) {
            $sts    = new StsClient(static::getS3Configuration());
            $cmd    = $sts->getCommand("GetSessionToken",
                                       [
                                           "DurationSeconds" => CarbonInterval::hours(8)->seconds,
                                       ]);
            $result = $sts->execute($cmd);

            $credentials[static::class] = [
                "Credentials" => $result['Credentials'],
                "expireAt"    => $now->addHour(8)->getTimestamp(),
            ];
        }

        return $credentials[static::class]['Credentials'];
    }

    /**
     * @return array
     */
    protected static function getS3Configuration()
    {
        throw new \LogicException("This method should be overriden");
    }
}
