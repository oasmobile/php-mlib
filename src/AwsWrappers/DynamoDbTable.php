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
    const PRIMARY_INDEX = false;

    /** @var DynamoDbClient */
    protected $db_client;

    protected $config;

    protected $table_name;
    protected $cas_field       = '';
    protected $attribute_types = [];

    function __construct(array $aws_config, $table_name, $attribute_types = [], $cas_field = '')
    {
        $this->config          = $aws_config;
        $this->db_client       = new DynamoDbClient($this->config);
        $this->table_name      = $table_name;
        $this->attribute_types = $attribute_types;
        $this->cas_field       = $cas_field;
    }

    public function setAttributeType($name, $type)
    {
        $this->attribute_types[$name] = $type;

        return $this;
    }

    public function get(array $keys, $is_consistent_read = false)
    {
        $keyItem     = DynamoDbItem::createFromArray($keys, $this->attribute_types);
        $requestArgs = [
            "TableName" => $this->table_name,
            "Key"       => $keyItem->getData(),
        ];
        if ($is_consistent_read) {
            $requestArgs["ConsistentRead"] = true;
        }

        $result = $this->db_client->getItem($requestArgs);
        if ($result['Item']) {
            $item = DynamoDbItem::createFromTypedArray((array)$result['Item']);

            return $item->toArray();
        }
        else {
            return null;
        }
    }

    public function set(array $obj, $cas = false)
    {
        $requestArgs = [
            "TableName" => $this->table_name,
        ];

        if ($this->cas_field) {
            $old_cas               = $obj[$this->cas_field];
            $obj[$this->cas_field] = time();

            if ($old_cas && $cas) {
                $requestArgs['ConditionExpression']       = "#CAS = :cas_val";
                $requestArgs['ExpressionAttributeNames']  = ["#CAS" => $this->cas_field];
                $requestArgs['ExpressionAttributeValues'] = [":cas_val" => ["N" => strval(intval($old_cas))]];
            }
        }
        $item                = DynamoDbItem::createFromArray($obj, $this->attribute_types);
        $requestArgs['Item'] = $item->getData();

        try {
            $this->db_client->putItem($requestArgs);
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

        $requestArgs = [
            "TableName" => $this->table_name,
            "Key"       => $keyItem->getData(),
        ];

        $result = $this->db_client->deleteItem($requestArgs);
        var_dump($result);
    }

    public function query($conditions,
                          array $fields,
                          array $params,
                          $index_name = self::PRIMARY_INDEX,
                          &$last_key = null,
                          $page_limit = 30,
                          $consistent_read = false)
    {
        $paramsItem = DynamoDbItem::createFromArray($params);

        $requestArgs = [
            "TableName"                 => $this->table_name,
            "KeyConditionExpression"    => $conditions,
            "ConsistentRead"            => $consistent_read,
            "ExpressionAttributeNames"  => $fields,
            "ExpressionAttributeValues" => $paramsItem->getData(),
        ];
        if ($index_name != self::PRIMARY_INDEX) {
            $requestArgs['IndexName'] = $index_name;
        }
        if ($last_key) {
            $requestArgs['ExclusiveStartKey'] = $last_key;
        }
        if ($page_limit) {
            $requestArgs['Limit'] = $page_limit;
        }
        var_dump($requestArgs);

        $result   = $this->db_client->query($requestArgs);
        $last_key = $result['LastEvaluatedKey'];
        $items    = $result['Items'];

        $ret = [];
        foreach ($items as $itemArray) {
            $item  = DynamoDbItem::createFromTypedArray($itemArray);
            $ret[] = $item->toArray();
        }

        return $ret;
    }

    public function queryAndRun(callable $callback,
                                $conditions,
                                array $fields,
                                array $params,
                                $index_name = self::PRIMARY_INDEX,
                                $consistent_read = false)
    {
        $last_key = null;
        do {
            $items = $this->query($conditions, $fields, $params, $index_name, $last_key, 30, $consistent_read);
            foreach ($items as $item) {
                call_user_func($callback, $item);
            }
        } while ($last_key != null);
    }

    public function scan($conditions = '',
                         array $fields = [],
                         array $params = [],
                         &$last_key = null,
                         $page_limit = 30)
    {
        $requestArgs = [
            "TableName" => $this->table_name,
        ];
        if ($conditions) {
            $requestArgs['FilterExpression'] = $conditions;
            if ($params) {
                $paramsItem                               = DynamoDbItem::createFromArray($params);
                $requestArgs['ExpressionAttributeValues'] = $paramsItem->getData();
            }
            if ($fields) {
                $requestArgs['ExpressionAttributeNames'] = $fields;
            }
        }
        if ($last_key) {
            $requestArgs['ExclusiveStartKey'] = $last_key;
        }
        if ($page_limit) {
            $requestArgs['Limit'] = $page_limit;
        }

        $result   = $this->db_client->scan($requestArgs);
        $last_key = $result['LastEvaluatedKey'];
        $items    = $result['Items'];

        $ret = [];
        foreach ($items as $itemArray) {
            $item  = DynamoDbItem::createFromTypedArray($itemArray);
            $ret[] = $item->toArray();
        }

        return $ret;
    }

    public function scanAndRun(callable $callback,
                               $conditions = '',
                               array $fields = [],
                               array $params = [])
    {
        $last_key = null;
        do {
            $items = $this->scan($conditions, $fields, $params, $last_key, 30);
            foreach ($items as $item) {
                call_user_func($callback, $item);
            }
        } while ($last_key != null);
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

    /**
     * @return DynamoDbClient
     */
    public function getDbClient()
    {
        return $this->db_client;
    }
}
