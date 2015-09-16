<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 16:54
 */

namespace Oasis\Mlib\Data;

class DataPacker
{
    protected $serializer   = "igbinary_serialize";
    protected $unserializer = "igbinary_unserialize";

    function __construct($serializer = null, $unserializer = null)
    {
        if (is_callable($serializer)) $this->serializer = $serializer;
        if (is_callable($unserializer)) $this->unserializer = $unserializer;
    }

    public function pack($dataObject)
    {
        $serialized = call_user_func($this->serializer, $dataObject);
        $len        = strlen($serialized);
        $header     = pack('N', $len);

        return $header . $serialized;
    }

    public function packToStream($fh, $dataObject)
    {
        $data = $this->pack($dataObject);
        fwrite($fh, $data);
    }

    public function unpack($data)
    {
        $header   = substr($data, 0, 4);
        $unpacked = unpack('Nlen', $header);
        $len      = $unpacked['len'];
        $payroll  = substr($data, 4);
        if ($len != strlen($payroll)) {
            throw new \UnexpectedValueException("Data to be unpacked has different length than what is specified in header.");
        }

        $unserialized = call_user_func($this->unserializer, $payroll);

        return $unserialized;
    }

    public function unpackFromStream($fh)
    {
        $header = fread($fh, 4);
        if ($header === false) {

            throw new \UnexpectedValueException("Cannot read header from stream");
        }
        if (strlen($header) < 4) return false;

        $unpacked = unpack('Nlen', $header);
        $len      = $unpacked['len'];
        $payroll  = '';
        while ($len > 0 && !feof($fh)) {
            $buffer = fread($fh, $len);
            if ($buffer === false) {
                throw new \UnexpectedValueException("Cannot read payroll data from stream");
            }
            $payroll .= $buffer;
            $len -= strlen($buffer);
        }

        if ($len > 0) {
            throw new \UnexpectedValueException("Not sufficient data in stream");
        }

        $unserialized = call_user_func($this->unserializer, $payroll);

        return $unserialized;
    }
}
