<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-11-09
 * Time: 13:38
 */

namespace Oasis\Mlib\Cli;

class MemoryMonitor
{
    public static function monitorMemoryUsage()
    {
        static $min_usage = 128000000;
        static $is_lowest = false;
        static $upper_threshold = 49; //percent threshold before adding more memory;
        static $lower_threshold = 10; //percent threshold before reducing more memory;
        static $never_reset = true;

        $cur  = memory_get_usage();
        $val  = ini_get('memory_limit');
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val = substr($val, 0, (strlen($val) - 1));
                $val *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $val = substr($val, 0, (strlen($val) - 1));
                $val *= 1024 * 1024;
                break;
            case 'k':
                $val = substr($val, 0, (strlen($val) - 1));
                $val *= 1024;
                break;
        }
        $tot          = $val;
        $usage        = $cur / $val * 100;
        $reset_needed = false;
        if ($usage > $upper_threshold) {
            $tot          = $val * 1.5;
            $is_lowest    = false;
            $reset_needed = true;
        }
        else if ($usage < $lower_threshold && !$never_reset && !$is_lowest) {
            $tot = $val * 0.7;
            if ($tot < $min_usage) {
                $tot       = $min_usage;
                $is_lowest = true;
            }
            $reset_needed = true;
        }

        if ($reset_needed) {
            $unit = "";
            if ($tot > 1024) {
                $tot  = ceil($tot / 1024);
                $unit = 'K';
            }
            if ($tot > 1024) {
                $tot  = ceil($tot / 1024 * 100) / 100;
                $unit = 'M';
            }
            if ($tot > 1024) {
                $tot  = ceil($tot / 1024 * 100) / 100;
                $unit = 'G';
            }
            $tot = $tot . $unit;
            ini_set('memory_limit', $tot);
            minfo("memory limit adjusted dynamically - $tot (from $val), cur = $cur");
            $never_reset = false;
        }
    }

    public static function registerMonitorForTick()
    {
        $function_name = __CLASS__ . "::monitorMemoryUsage";
        register_tick_function($function_name);
    }
}
