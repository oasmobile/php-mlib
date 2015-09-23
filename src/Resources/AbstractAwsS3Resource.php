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
use League\Flysystem\Filesystem;
use Oasis\Mlib\FlysystemWrappers\FixedAwsS3Adapter;

abstract class AbstractAwsS3Resource extends AbstractResourcePoolBase
{
    /** @var S3Client[] */
    protected $s3clients = [];

    /** @var array[] */
    protected $credentials = [];

    /** @var array */
    protected $registeredWrappers = [];

    public function createResource($key = '')
    {
        $config = $this->getConfig($key);

        $adapter = new FixedAwsS3Adapter(
            $this->getS3Client($key),
            $config['bucket'],
            $config['prefix']
        );

        return new Filesystem($adapter);
    }

    public function getS3Client($key = '')
    {
        if ($this->s3clients[$key] instanceof S3Client) {
            return $this->s3clients[$key];
        }

        $this->s3clients[$key] = new S3Client($this->getConfig($key));

        return $this->s3clients[$key];
    }

    public function registerStreamWrapper($key = '', $protocol = "s3")
    {
        if ($this->registeredWrappers[$protocol] === $key) {
            return true;
        }

        if (isset($this->registeredWrappers[$protocol])) {
            throw new \RuntimeException("Protocol $protocol:// is already registered to another s3 resource <$key>");
        }

        StreamWrapper::register($this->getS3Client($key), $protocol);

        $this->registeredWrappers[$protocol] = $key;

        return true;
    }

    public function getTemporaryCredentials($key)
    {
        $now         = Carbon::now();
        $minExpireAt = $now->addHour(3);
        if (!$this->credentials[$key]
            || !$this->credentials[$key]['expireAt']
            || $this->credentials[$key]['expireAt'] < $minExpireAt->getTimestamp()
        ) {
            $sts    = new StsClient($this->getConfig($key));
            $cmd    = $sts->getCommand("GetSessionToken",
                                       [
                                           "DurationSeconds" => 8 * 3600,
                                       ]);
            $result = $sts->execute($cmd);

            $this->credentials[$key] = [
                "Credentials" => $result['Credentials'],
                "expireAt"    => $now->addHour(8)->getTimestamp(),
            ];
        }

        return $this->credentials[$key]['Credentials'];
    }
}
