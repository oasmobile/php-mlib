<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-08
 * Time: 11:29
 */
namespace Oasis\Mlib\AwsWrappers;

class Utilities
{
    static public function formatObjectToRedshiftLine($obj, &$fields)
    {
        $patterns     = [
            "/\\\\/",
            "/\n/",
            "/\r/",
            "/\\|/",
        ];
        $replacements = [
            "\\\\\\\\",
            "\\\n",
            "\\\r",
            "\\|",
        ];

        $line = '';
        foreach ($fields as $k) {
            if ($line !== '') $line .= "|";

            $v = $obj->$k;
            $v = preg_replace($patterns, $replacements, $v);
            $line .= $v;
        }

        return $line;
    }

}
