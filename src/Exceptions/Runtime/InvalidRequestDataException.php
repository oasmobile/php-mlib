<?php

/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-15
 * Time: 11:34
 */
namespace Oasis\Mlib\Exceptions\Runtime;

use Exception;
use RuntimeException;

/**
 * Class InvalidRequestDataException
 *
 * Thrown when request data is malformed
 */
class InvalidRequestDataException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
