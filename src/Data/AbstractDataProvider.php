<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-15
 * Time: 11:48
 */
namespace Oasis\Mlib\Data;

abstract class AbstractDataProvider
{
    const INT_TYPE    = "requireInt";
    const FLOAT_TYPE  = "requireFloat";
    const STRING_TYPE = "requireString";
    const ARRAY_TYPE  = "requireArray";
    const BOOL_TYPE   = "requireBool";

    /**
     * @param            $key
     * @param            $type
     * @param bool|false $isMandatory
     * @param null       $default
     *
     * @return mixed
     */
    public function get($key, $type = self::STRING_TYPE, $isMandatory = false, $default = null)
    {
        $value = $this->getValue($key);

        if ($value === null) {
            if ($isMandatory) {
                throw new MandatoryValueMissingException("Mandatory value $key is missing in data");
            }
            else {
                $value = $default;
            }
        }

        if (!in_array($type,
                      [
                          self::INT_TYPE,
                          self::FLOAT_TYPE,
                          self::STRING_TYPE,
                          self::ARRAY_TYPE,
                          self::BOOL_TYPE,
                      ])
        ) {
            throw new InvalidDataTypeException("Type $type is not an allowed type");
        }

        $typed_value = $this->{$type}($value);

        return $typed_value;
    }

    public function getMandatory($key, $type = self::STRING_TYPE)
    {
        return $this->get($key, $type, true);
    }

    public function getOptional($key, $type = self::STRING_TYPE, $default = null)
    {
        return $this->get($key, $type, false, $default);
    }

    /**
     * @param string $key the key to be used to read a value from the data provider
     *
     * @return mixed|null       null indicates the value is not presented in the data provider
     */
    abstract protected function getValue($key);

    protected function requireInt(&$val)
    {
        if (is_null($val)) return 0;

        if (!is_scalar($val)) {
            throw new InvalidDataTypeException("Non-scalar value encountered when int is expected");
        }

        return intval($val);
    }

    protected function requireFloat(&$val)
    {
        if (is_null($val)) return (float)0;

        if (!is_scalar($val)) {
            throw new InvalidDataTypeException("Non-scalar value encountered when float is expected");
        }

        return floatval($val);
    }

    protected function requireString(&$val)
    {
        if (is_array($val)) {
            throw new InvalidDataTypeException("Array value is encountered when string is expected");
        }
        if (is_object($val) && !method_exists($val, "__toString")) {
            throw new InvalidDataTypeException("Object doesn't support __toString() method when string value is expected");
        }

        return strval($val);
    }

    protected function requireArray(&$val)
    {
        if (is_null($val)) return [];

        if (!is_array($val)) {
            throw new InvalidDataTypeException("Non array encountered when array is expected");
        }

        return $val;
    }

    protected function requireBool(&$val)
    {
        if (is_bool($val)) return $val;

        if (is_string($val)) {
            switch (strtolower($val)) {
                case "on":
                case "true":
                case "yes":
                    return true;
                    break;
                case "off":
                case "false":
                case "no":
                    return false;
                    break;
                default:
                    throw new InvalidDataTypeException("Unknown string encountered for bool val, string = $val");
                    break;
            }
        }

        return intval($val) ? true : false;
    }
}
