<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-29
 * Time: 14:48
 */

namespace Oasis\Mlib\AwsWrappers;

use Oasis\Mlib\Data\InvalidDataTypeException;

class DynamoDbItem
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

    protected $data = [];

    public static function createFromTypedArray(array &$typed_value)
    {
        if (!$typed_value) {
            return null;
        }
        $ret       = new static;
        $ret->data = $typed_value;

        return $ret;
    }

    public static function createFromArray(array &$normal_value, $known_types = [])
    {
        $ret = new static;
        foreach ($normal_value as $k => $v) {
            $ret->data[$k] = static::toTypedValue($v, $known_types[$k]);
        }

        return $ret;
    }

    protected static function toUntypedValue(&$v)
    {
        if (!is_array($v) || count($v) != 1) {
            throw new InvalidDataTypeException("Value used is not typed value, value = " . mdump($v));
        }
        $value = reset($v);
        $type  = key($v);

        switch ($type) {
            case self::ATTRIBUTE_TYPE_STRING:
                return strval($value);
                break;
            case self::ATTRIBUTE_TYPE_BINARY:
                return base64_decode($value);
                break;
            case self::ATTRIBUTE_TYPE_BOOL:
                return boolval($value);
                break;
            case self::ATTRIBUTE_TYPE_NULL:
                return null;
                break;
            case self::ATTRIBUTE_TYPE_NUMBER:
                if (intval($value) == $value) {
                    return intval($value);
                }
                else {
                    return floatval($value);
                }
                break;
            case self::ATTRIBUTE_TYPE_LIST:
            case self::ATTRIBUTE_TYPE_MAP:
                if (!is_array($value)) {
                    throw new InvalidDataTypeException("The value is expected to be an array! $v = " . mdump($v));
                }
                $ret = [];
                foreach ($value as $k => &$vv) {
                    $ret[$k] = static::toUntypedValue($vv);
                }

                return $ret;
                break;
            default:
                throw new InvalidDataTypeException("Type $type is not recognized!");
                break;
        }
    }

    protected static function toTypedValue(&$v, $type = null)
    {
        if (!$type) {
            $type = static::determineAttributeType($v);
        }

        switch ($type) {
            case self::ATTRIBUTE_TYPE_STRING: {
                if (!$v) {
                    return [self::ATTRIBUTE_TYPE_NULL => true];
                }
                else {
                    return [$type => strval($v)];
                }
            }
                break;
            case self::ATTRIBUTE_TYPE_BINARY: {
                if (!$v) {
                    return [self::ATTRIBUTE_TYPE_NULL => true];
                }
                else {
                    return [$type => base64_encode($v)];
                }
            }
                break;
            case self::ATTRIBUTE_TYPE_BOOL:
                return [$type => boolval($v)];
                break;
            case self::ATTRIBUTE_TYPE_NULL:
                return [$type => true];
                break;
            case self::ATTRIBUTE_TYPE_NUMBER:
                if (!is_numeric($v)) {
                    return [$type => "0"];
                }
                elseif (intval($v) == $v) {
                    return [$type => strval($v)];
                }
                else {
                    return [$type => strval(floatval($v))];
                }
                break;
            case self::ATTRIBUTE_TYPE_LIST:
            case self::ATTRIBUTE_TYPE_MAP: {
                $children = [];
                foreach ($v as $k => &$vv) {
                    $children[$k] = static::toTypedValue($vv);
                }

                return [$type => $children];
                break;
            }
            default:
                throw new \RuntimeException("Unknown type for dynamodb item, value = $v");
        }
    }

    protected static function determineAttributeType(&$v)
    {
        if (is_numeric($v)) {
            return self::ATTRIBUTE_TYPE_NUMBER;
        }
        elseif (is_bool($v)) {
            return self::ATTRIBUTE_TYPE_BOOL;
        }
        elseif (is_null($v)) {
            return self::ATTRIBUTE_TYPE_NULL;
        }
        elseif (is_array($v)) {
            $idx = 0;
            /** @noinspection PhpUnusedLocalVariableInspection */
            foreach ($v as $k => &$vv) {
                if ($k !== $idx) {
                    return self::ATTRIBUTE_TYPE_MAP;
                }
                $idx++;
            }

            return self::ATTRIBUTE_TYPE_LIST;
        }
        else {
            return self::ATTRIBUTE_TYPE_STRING;
        }
    }

    public function toArray()
    {
        $ret = [];
        foreach ($this->data as $k => &$v) {
            $ret[$k] = static::toUntypedValue($v);
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}