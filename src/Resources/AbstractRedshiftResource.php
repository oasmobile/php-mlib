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

abstract class AbstractRedshiftResource extends AbstractResourcePoolBase
{
    public function createResource($key = '')
    {
        $connectionParams =
            $this->getConfig($key)
            + [
                'driver' => 'pdo_pgsql',
            ];

        return DriverManager::getConnection($connectionParams);
    }

    /**
     * @param string $key
     *
     * @return Connection
     */
    public function getResource($key = '')
    {
        return parent::getResource($key);
    }

}
