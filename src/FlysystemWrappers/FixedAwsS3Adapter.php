<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:25
 */
namespace Oasis\Mlib\FlysystemWrappers;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Util;

/**
 * Class FixedAwsS3Adapter
 *
 * @package Oasis\Mlib\FlysystemWrappers
 */
class FixedAwsS3Adapter extends AwsS3Adapter
{

    /**
     * @inheritdoc
     */
    public function __construct(S3Client $client, $bucket, $prefix = '')
    {
        parent::__construct($client, $bucket, $prefix);
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        // since flysystem/aws-s3-v3 1.0.6, the bug is fixed.
        /*
        // @NOTE: this fixes a bug in AwsS3Adapter (flysystem/aws-s3-v3) v1.0.5
        // if future release of AwsS3Adapater fixes this, we should remove this override
        return parent::has($this->applyPathPrefix($path));
        //*/
        return parent::has($path);
    }
}
