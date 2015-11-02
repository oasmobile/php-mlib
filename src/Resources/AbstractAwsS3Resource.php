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
use League\Flysystem\Filesystem;
use League\Flysystem\Plugin\ListPaths;
use Oasis\Mlib\FlysystemWrappers\FixedAwsS3Adapter;
use Symfony\Component\Finder\Finder;

abstract class AbstractAwsS3Resource extends AbstractResourcePoolBase
{
    /** @var S3Client[] */
    protected $s3clients = [];

    /** @var array[] */
    protected $credentials = [];

    /** @var array */
    protected $registeredWrappers = [];

    /**
     * @param string $key
     *
     * @return Filesystem
     */
    public function getResource($key = '')
    {
        return parent::getResource($key);
    }

    public function createResource($key = '')
    {
        $config  = $this->getConfig($key);
        $adapter = new FixedAwsS3Adapter(
            $this->getS3Client($key),
            $config['bucket'],
            $config['prefix']
        );
        $fs      = new Filesystem($adapter);
        $fs->addPlugin(new ListPaths());

        return $fs;
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
        if (isset($this->registeredWrappers[$protocol])) {
            if ($this->registeredWrappers[$protocol] === $key) {
                return true;
            }

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
            $cmd    = $sts->getCommand(
                "GetSessionToken",
                [
                    "DurationSeconds" => 8 * 3600,
                ]
            );
            $result = $sts->execute($cmd);

            $this->credentials[$key] = [
                "Credentials" => $result['Credentials'],
                "expireAt"    => $now->addHour(8)->getTimestamp(),
            ];
        }

        return $this->credentials[$key]['Credentials'];
    }

    public function finder($key = '')
    {
        static $count = 0;
        $count++;
        if (($protocol = array_search($key, $this->registeredWrappers))
            === false
        ) {
            $protocol = "s3-finder-{$count}";
            $this->registerStreamWrapper($key, $protocol);
        }

        /** @var Filesystem $fs */
        $fs = $this->getResource($key);
        /** @var FixedAwsS3Adapter $adapter */
        $adapter = $fs->getAdapter();

        $finder = new Finder();
        $finder->in(
            $protocol . "://"
            . $this->getConfig($key)['bucket'] . "/"
            . $adapter->applyPathPrefix("")
        );

        return $finder;
    }
}
