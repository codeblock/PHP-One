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
    
    //arraySearchByRange
    
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
    
    //randByRangeOfSum
}