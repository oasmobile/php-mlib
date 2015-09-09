<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:32
 */
namespace Oasis\Mlib\FlysystemWrappers;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\NotSupportedException;
use League\Flysystem\Util;

class AppendableFilesystem extends Filesystem
    implements AppendableFilesystemInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(AdapterInterface $adapter, $config = null)
    {
        parent::__construct($adapter, $config);
    }

    /**
     * @inheritdoc
     */
    public function append($path, $contents, array $config = [])
    {
        $path   = Util::normalizePath($path);
        $config = $this->prepareConfig($config);

        $adapter = $this->getAdapter();
        if (!$adapter instanceof AppendableAdapterInterface) {
            throw new NotSupportedException("Adapter doesn't support append action. Adapter in use is: "
                                            . get_class($adapter));
        }

        return (bool)$adapter->append($path, $contents, $config);
    }

    /**
     * @inheritdoc
     */
    public function appendStream($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        $adapter = $this->getAdapter();
        if (!$adapter instanceof AppendableAdapterInterface) {
            throw new NotSupportedException("Adapter doesn't support append action. Adapter in use is: "
                                            . get_class($adapter));
        }

        if (!$object = $adapter->appendStream($path)) {
            return false;
        }

        return $object['stream'];
    }
}
