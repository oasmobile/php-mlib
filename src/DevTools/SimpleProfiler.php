<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-11-10
 * Time: 12:00
 */

namespace Oasis\Mlib\DevTools;

class SimpleProfiler
{
    protected static $logPoints = [];
    protected static $history   = [];

    public static function start()
    {
        $callStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
        foreach ($callStack as &$trace) {
            if (dirname($trace['file']) == __FILE__) {
                continue;
            }

            $info              = [
                "file"     => basename($trace['file']),
                "line"     => $trace['line'],
                'start'    => microtime(true),
            ];
            self::$logPoints[] = $info;
            break;
        }
    }

    public static function end()
    {
        if (!self::$logPoints) {
            mwarning("There is no log point available.");

            return;
        }

        $last_point         = array_pop(self::$logPoints);
        $last_point['end']  = microtime(true);
        $last_point['cost'] = $last_point['end'] - $last_point['start'];

        $key                   = $last_point['file'] . ":" . $last_point['line'];
        self::$history[$key][] = $last_point;
    }

    public static function getStats($sortAvg = true)
    {
        $ret = [];
        foreach (self::$history as $key => &$infoList) {
            $total    = 0;
            $count    = count($infoList);
            $lastInfo = null;
            foreach ($infoList as &$info) {
                $total += $info['cost'];
                $lastInfo = &$info;
            }
            $ret[] = [
                "file"     => $lastInfo['file'],
                "line"     => $lastInfo['line'],
                "total"    => round($total * 1000 * 100) / 100,
                "avg"      => round($total / $count * 1000 * 100) / 100,
            ];
        }

        $keyField = $sortAvg ? 'avg' : 'total';
        usort(
            $ret,
            function ($a, $b) use ($keyField) {
                if ($a[$keyField] < $b[$keyField]) {
                    return 1;
                }
                elseif ($a[$keyField] == $b[$keyField]) {
                    return 0;
                }
                else {
                    return -1;
                }
            }
        );

        return $ret;
    }
}
