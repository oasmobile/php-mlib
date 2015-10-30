<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-29
 * Time: 14:07
 */

namespace Oasis\Mlib\AwsWrappers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

class DynamoDbTable
{
    const ATTRIBUTE_TYPE_STRING = 'S';
    const ATTRIBUTE_TYPE_BINARY = 'B';
    const ATTRIBUTE_TYPE_NUMBER = 'N';
//    const ATTRIBUTE_TYPE_STRING_SET = 'SS';
//    const ATTRIBUTE_TYPE_BINARY_SET = 'BS';
//    const ATTRIBUTE_TYPE_NUMBER_SET = 'NS';
    const ATTRIBUTE_TYPE_LIST = 'L';
    const ATTRIBUTE_TYPE_MAP  = 'M';
    const ATTRIBUTE_TYPE_BOOL = 'BOOL';
    const ATTRIBUTE_TYPE_NULL = 'NULL';

    /** @var DynamoDbClient */
    protected $db_client;

    protected $config;

    protected $table_name;
    protected $cas_field       = null;
    protected $attribute_types = [];

    function __construct(array $aws_config, $table_name, $attribute_types = [])
    {
        $this->db_client       = new DynamoDbClient($aws_config);
        $this->config          = $aws_config;
        $this->table_name      = $table_name;
        $this->attribute_types = $attribute_types;
    }

    public function setAttributeType($name, $type)
    {
        $this->attribute_types[$name] = $type;

        return $this;
    }

    public function get($keys, $is_consistent_read = false)
    {
        $keyItem = DynamoDbItem::createFromArray($keys, $this->attribute_types);
        $params  = [
            "TableName" => $this->table_name,
            "Key"       => $keyItem->getData(),
        ];
        if ($is_consistent_read) {
            $params["ConsistentRead"] = true;
        }

        $result = $this->db_client->getItem($params);
        $item   = DynamoDbItem::createFromTypedArray($result['Item']);
        if ($item instanceof DynamoDbItem) {
            return $item->toArray();
        }
        else {
            return null;
        }
    }

    public function set(array &$obj, $cas = false)
    {
        $params = [
            "TableName" => $this->table_name,
        ];

        if ($this->cas_field) {
            $old_cas               = $obj[$this->cas_field];
            $obj[$this->cas_field] = time();

            if ($old_cas && $cas) {
                $params['ConditionExpression']       = "#CAS = :cas_val";
                $params['ExpressionAttributeNames']  = ["#CAS" => $this->cas_field];
                $params['ExpressionAttributeValues'] = [":cas_val" => ["N" => strval(intval($old_cas))]];
            }
        }
        $item           = DynamoDbItem::createFromArray($obj, $this->attribute_types);
        $params['Item'] = $item->getData();

        try {
            var_dump($params);
            $this->db_client->putItem($params);
        } catch (DynamoDbException $e) {
            if ($e->getAwsErrorCode() == "ConditionalCheckFailedException") {
                return false;
            }
            mtrace(
                $e,
                "Exception while setting dynamo db item, aws code = "
                . $e->getAwsErrorCode()
                . ", type = "
                . $e->getAwsErrorType()
            );
            throw $e;
        }

        return true;
    }

    public function delete($keys)
    {
        $keyItem = DynamoDbItem::createFromArray($keys, $this->attribute_types);

        $this->db_client->deleteItem(
            [
                "TableName" => $this->table_name,
                "Key"       => $keyItem->getData(),
            ]
        );
    }

    /**
     * @return string
     */
    public function getCasField()
    {
        return $this->cas_field;
    }

    /**
     * @param string $cas_field
     */
    public function setCasField($cas_field)
    {
        $this->cas_field = $cas_field;
    }
}
