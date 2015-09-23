<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-23
 * Time: 15:01
 */

namespace Oasis\Mlib\Resources;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class AbstractRedshiftResource
{
    public static function getFilesystem()
    {
        /** @var Filesystem[] $instances */
        static $instances = [];
        if (!$instances[static::class] instanceof Connection) {
            $connectionParams         =
                static::getRedshiftConfiguration()
                + [
                    'driver' => 'pdo_pgsql',
                ];
            $instances[static::class] = DriverManager::getConnection($connectionParams);
        }

        return $instances[static::class];
    }

    /**
     * @return array
     */
    protected static function getRedshiftConfiguration()
    {
        throw new \LogicException("This method should be overriden");
    }
}
