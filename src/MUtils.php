<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-15
 * Time: 21:10
 */
namespace Oasis\Mlib;

use voku\helper\UTF8;

class MUtils
{
    public static function stringChopdown($str, $maxLength)
    {
        $str = UTF8::to_utf8($str);
        $len = UTF8::strlen($str);
        if ($len <= $maxLength) {
            return $str;
        }

        return UTF8::substr($str, 0, $maxLength);
    }

    public static function stringStartsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return
            $needle === ""
            || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public static function stringEndsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return
            $needle === ""
            || (
                ($temp = strlen($haystack) - strlen($needle)) >= 0
                && strpos($haystack, $needle, $temp) !== false
            );
    }

    public static function rc4($key, $input)
    {
        $s = [];
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j     = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
            $x     = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
        }
        $i   = 0;
        $j   = 0;
        $res = '';
        for ($y = 0; $y < strlen($input); $y++) {
            $i     = ($i + 1) % 256;
            $j     = ($j + $s[$i]) % 256;
            $x     = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $res .= chr(ord($input[$y]) ^ $s[($s[$i] + $s[$j]) % 256]);
            //$res .= $input[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
        }

        return $res;
    }
}
