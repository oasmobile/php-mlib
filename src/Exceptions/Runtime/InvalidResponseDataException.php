<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-10
 * Time: 19:53
 */

namespace Oasis\Mlib\Exceptions\Runtime;

use Exception;

class InvalidResponseDataException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
}
