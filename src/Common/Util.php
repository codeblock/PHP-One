<?php
namespace Common;

use Common\Debug;

/**
 * Utility class
 *
 * @author beanfondue@gmail.com
 *
 */
class Util
{
    public static function stringStartsWith($haystack, $needle)
    {
        return (strpos($haystack, $needle) === 0);
    }
    
    public static function stringContains($haystack, $needle)
    {
        return (strpos($haystack, $needle) !== false);
    }
    
    public static function stringEndsWith($haystack, $needle)
    {
        $rpos = strrpos($haystack, $needle);
        
        return (substr($haystack, $rpos) === $needle);
    }
    
    /**
     * @param string $format
     * @param int    $now
     * @return string
     */
    public static function date($format = 'Y-m-d H:i:s', $now = null)
    {
        if ($now === null) {
            return date($format);
        } else {
            return date($format, $now);
        }
    }
    
    public static function dateFromString($str, $format = 'Y-m-d H:i:s')
    {
        return self::date($format, self::timeFromString($str));
    }
    
    public static function time()
    {
        return time();
    }
    
    public static function timeFromString($str)
    {
        return strtotime($str);
    }
    
    public static function arrayMergeByKey($arr_to, $arr_from)
    {
        if (is_array($arr_to) === true && is_array($arr_from) === true) {
            return array_intersect_key(array_merge($arr_to, $arr_from), $arr_to);
        } else {
            return $arr_to;
        }
    }
    
    public static function arrayRemoveByValues($arr = [], $values = [])
    {
        $rtn = $arr;
        
        $index = 0;
        $found = 0;
        foreach ($rtn as $k => $v) {
            if (in_array($v, $values, true) === true) {
                array_splice($rtn, $index - $found, 1);
                $found++;
            }
            $index++;
        }
        
        return $rtn;
    }
    
    public static function arrayRemovesByValues($arrs = [[]], $values = [])
    {
        $rtn = $arrs;
        
        foreach ($rtn as $k => $v) {
            $rtn[$k] = self::arrayRemoveByValues($v, $values);
        }
        
        return $rtn;
    }
    
    //arrayChangeKeys
    
    //arrayChangesKeys
    
    //arrayMapsByKey
    
    /**
     * search the key of array using binary search
     *
     * must be already sorted set on caller
     * if needs range search, it can be controlled by $comparison parameter
     * Warning: if use for equal comparison only, using the array_search for performance
     *
     * @param array  $arr        row for find
     * @param number $search     value for find with $comparison
     * @param string $comparison =, <, >, <=, >=
     * @return mixed $rtn        key of row or null
     */
    public static function arraySearchByRange($arr = [], $search = null, $comparison = '=')
    {
        $rtn = null;
        $idx = null;
        
        if (is_array($arr) === false || (is_numeric($search) === false && is_string($search) === false)) {
            return $rtn;
        }
        
        // if use for equal comparison only, using the array_search for performance
        //if ($comparison === '=') {
        //    $idx = array_search($search, $arr, true);
        //    if ($idx !== false) {
        //        $rtn = $idx;
        //    }
        //
        //    return $rtn;
        //}
        
        // must be already sorted set on caller
        //asort($arr);
        
        // Basically, should be indexed array, and it must be doesn't needs phrases below for performance.
        // now, associative array supported
        //595f998fcade94 $vals = $arr;
        $keys = array_keys($arr);
        $vals = array_values($arr);
        
        $cnt     = count($vals) - 1;
        $range_s = (int)floor(($cnt - 1) / 2);
        $range_e = $cnt;
        
        if ($vals[$range_s] > $search) {
            $range_e -= $range_s;
            $range_s = 0;
        }
        
        $repeat  = 0;
        
        //dbg $analysis = [];
        //dbg $analysis['sample']     = $arr;
        //dbg $analysis['search']     = $search;
        //dbg $analysis['comparison'] = $comparison;
        //dbg $analysis['retry']      = [];
        //dbg $analysis['retry'][]    = '[base] range_s: ' . $range_s . ', range_e: ' . $range_e;
        
        while (true) {
            if ($repeat >= 100) {
                Debug::trace('this algorythm has problems');
                break;
            }
            $repeat++;
            
            $mid = (int)floor(($range_e - $range_s) / 2);
            if ($vals[$range_s + $mid] < $search) {
                $range_s += $mid;
            } else {
                $range_e -= $mid;
            }
            
            //dbg $analysis['retry'][] = '[loop] range_s: ' . $range_s . ', range_e: ' . $range_e;
            
            if ($range_e - $range_s === 1) {
                switch ($comparison) {
                    case '<':
                        if ($search < $vals[$range_s]) {
                            $idx = $range_s;
                        } else if ($vals[$range_s] <= $search && $search < $vals[$range_e]) {
                            $idx = $range_e;
                        } else if ($vals[$range_e] <= $search) {
                            $idx = $range_e + 1;
                        }
                        break;
                    case '>':
                        if ($search > $vals[$range_e]) {
                            $idx = $range_e;
                        } else if ($vals[$range_e] >= $search && $search > $vals[$range_s]) {
                            $idx = $range_s;
                        } else if ($vals[$range_s] >= $search) {
                            $idx = $range_s - 1;
                        }
                        break;
                    case '<=':
                        if ($search <= $vals[$range_s]) {
                            $idx = $range_s;
                        } else if ($vals[$range_s] < $search && $search <= $vals[$range_e]) {
                            $idx = $range_e;
                        } else if ($vals[$range_e] < $search) {
                            $idx = $range_e + 1;
                        }
                        break;
                    case '>=':
                        if ($search >= $vals[$range_e]) {
                            $idx = $range_e;
                        } else if ($vals[$range_e] > $search && $search >= $vals[$range_s]) {
                            $idx = $range_s;
                        } else if ($vals[$range_s] > $search) {
                            $idx = $range_s - 1;
                        }
                        break;
                    default: // case '='
                        if ($search === $vals[$range_s]) {
                            $idx = $range_s;
                        } else if ($search === $vals[$range_e]) {
                            $idx = $range_e;
                        }
                }
                break;
            }
        }
        
        //dbg $analysis['repeat'] = $repeat;
        
        //595f998fcade94 if (isset($vals[$idx]) === true) {
        if (isset($keys[$idx]) === true) {
            //dbg $analysis['result'] = $keys[$idx];
            //595f998fcade94 $rtn = $idx;
            $rtn = $keys[$idx];
        } else {
            //dbg $analysis['result'] = null;
            Debug::log('cannot find value');
        }
        
        //dbg Debug::log(print_r($analysis, true)); // as-is
        //dbg //Debug::log(json_encode($analysis)); // to-be
        
        return $rtn;
    }
    
    /**
     * timestamp for next initialization with adjusted timezone
     *
     * @param string $datetimes   yyyy-MM-dd hh:mm:ss (now for standards)
     * @param int    $margin_hour x (= x:00:00)
     * @return int
     */
    public static function initNextTime($datetimes = '', $margin_hour = 0)
    {
        $rtn = 0;
        
        $time_now = self::time();
        if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $datetimes)) {
            $time_now = self::timeFromString($datetimes);
        }
        
        $rtn = $time_now - $time_now % 86400 + 86400 + 3600 * $margin_hour;
        $rtn -= (int)date('Z'); // adjust from UTC offset
        if ($time_now > $rtn) {
            $rtn = $rtn + 86400;
        }
        
        return $rtn;
    }
    
    /**
     * remain seconds for next initialization
     *
     * @param string $datetimes   yyyy-MM-dd hh:mm:ss (now for standards)
     * @param int    $margin_hour x (= x:00:00)
     * @return int
     * @see
     *                   - init time : 00:00:00
     *                       - now (Asia/Seoul): 2016-11-30 18:10:30
     *                           - initRemainSeconds()                     : 20970 (05:49:30)
     *                           - initRemainSeconds('2016-09-01 01:00:00'): 82800 (23:00:00)
     *                       - now (UTC)       : 2016-11-30 09:10:37
     *                           - initRemainSeconds()                     : 53363 (14:49:23)
     *                           - initRemainSeconds('2016-09-01 01:00:00'): 82800 (23:00:00)
     */
    public static function initRemainSeconds($datetimes = '', $margin_hour = 0)
    {
        $nexttime = self::initNextTime($datetimes, $margin_hour);
        
        $time_now = self::time();
        if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $datetimes)) {
            $time_now = self::timeFromString($datetimes);
        }
        
        $rtn = $nexttime - $time_now;
        
        return $rtn;
    }
    
    /**
     * seconds to human readable
     *
     * @param  int       seconds
     * @return string    hh:mm:ss
     * @see
     *         396985 -> 110:16:25
     *         105362 -> 29:16:02
     */
    public static function sec2hms($seconds)
    {
        $hh = sprintf('%02s', floor($seconds / 3600));
        $mm = sprintf('%02s', floor($seconds / 60) % 60);
        $ss = sprintf('%02s', $seconds % 60);
        
        return $hh . ':' . $mm . ':' . $ss;
    }
    
    /**
     * hh:mm:ss to seconds
     * 
     * @param  string hh:mm:ss
     * @return int    seconds
     * @see
     *         110:16:25 -> 396985
     *         29:16:02 -> 105362
     */
    public static function hms2sec($hms = '00:00:00')
    {
        $hh = (int)strtok($hms, ':') * 3600;
        $mm = (int)strtok(':') * 60;
        $ss = (int)strtok(':');
        
        return $hh + $mm + $ss;
    }
    
    /**
     * is [[], [], ...]
     * 
     * @param array $arr
     * @return boolean
     */
    public static function isRows($arr)
    {
        return is_array($arr) === true && is_array(current($arr)) === true;
    }
    
    /**
     * is []
     * 
     * @param array $arr
     * @return boolean
     */
    public static function isRow($arr)
    {
        return is_array($arr) === true && is_array(current($arr)) === false;
    }
    
    public static function randByRangeOfSum($arr = [])
    {
        $rtn = null;
        
        if (is_array($arr) === false) {
            return $rtn;
        }
        
        asort($arr);
        
        $starts = min($arr);
        $ends   = array_sum($arr);
        $random = mt_rand($starts, $ends);
        
        $random = min($random, max($arr));
        
        //dbg Debug::log(json_encode($arr) . ' : ' . $random . ' (' . $starts . ' ~ ' . $ends . ')');
        
        return self::arraySearchByRange($arr, $random, '<=');
    }
}