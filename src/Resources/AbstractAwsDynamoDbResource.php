<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-11-02
 * Time: 09:59
 */

namespace Oasis\Mlib\Resources;

use Oasis\Mlib\AwsWrappers\DynamoDbTable;

abstract class AbstractAwsDynamoDbResource extends AbstractResourcePoolBase
{
    public function createResource($key = '')
    {
        $config = $this->getConfig($key);
        $table  = new DynamoDbTable(
            $config,
            $config['table_name'],
            $config['attributes'],
            $config['cas_field']
        );

        return $table;
    }

    /**
     * @param string $key
     *
     * @return DynamoDbTable
     */
    public function getResource($key = '')
    {
        return parent::getResource($key);
    }

}
