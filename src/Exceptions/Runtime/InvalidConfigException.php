<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-17
 * Time: 17:21
 */

namespace Oasis\Mlib\Exceptions\Runtime;

use Exception;

class InvalidConfigException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
}
