<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 19:02
 */

namespace Oasis\Mlib\Resources;

use League\Flysystem\Plugin\ListPaths;
use Oasis\Mlib\FlysystemWrappers\AppendableFilesystem;
use Oasis\Mlib\FlysystemWrappers\AppendableLocal;

class LocalDataStorageResource
{
    public static function getFilesystem()
    {
        /** @var AppendableFilesystem[] $fileSystems */
        static $fileSystems = null;
        if (!$fileSystems[static::class] instanceof AppendableFilesystem) {
            $adapter                    = new AppendableLocal(static::getLocalDataStoragePath());
            $fileSystems[static::class] = new AppendableFilesystem($adapter);
            $fileSystems[static::class]->addPlugin(new ListPaths());

        }

        return $fileSystems[static::class];
    }

    /**
     * @return string
     */
    protected static function getLocalDataStoragePath()
    {
        throw new \LogicException("This method should be overriden");
    }
}
