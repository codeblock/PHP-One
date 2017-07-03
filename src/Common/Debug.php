<?php
namespace Common;

/**
 * Debugging class
 *
 * @author beanfondue@gmail.com
 *
 */
class Debug
{
    private static function prefix()
    {
        $rtn = '';
        
        $trace = debug_backtrace();
        
        $from = 2;
        if (isset($trace[$from]) == false) {
            $from = 1;
        }
        
        $clnm = '';
        if (isset($trace[$from]['class']) === true) {
            $clnm = $trace[$from]['class'];
        } else {
            $clnm = basename($trace[$from]['file']);
        }
        
        $func = $trace[$from]['function'];
        $line = $trace[1]['line'];
        
        if ($from == 1 && !strcmp($clnm, __CLASS__)) {
            $clnm = $trace[$from]['file'];
            
            $posn = strrpos($clnm, DIRECTORY_SEPARATOR);
            $posn = ($posn !== false) ? $posn + 1 : 0;
            $clnm = substr($clnm, $posn);
            
            list($clnm, $ext) = explode('.', $clnm);
            
            $rtn = $clnm;
        } else {
            $posn = strrpos($clnm, '\\');
            $posn = ($posn !== false) ? $posn + 1 : 0;
            $clnm = substr($clnm, $posn);
            
            $rtn = $clnm . '.' . $func;
        }
        
        $rtn .= ':' . $line . ' - ';
        
        return $rtn;
    }
    
    public static function log($data = '')
    {
        error_log(FDEF_PREFIX_LOG . self::prefix() . $data);
    }
    
    public static function trace($cause = '')
    {
        $rtn = [];
        
        $arr = debug_backtrace();
        $n   = 0;
        
        foreach ($arr as $k => $v) {
            $n = $k - 1;
            
            if (0 == $k) {
                $fired = "\t##  fired at [{$v['file']}:{$v['line']}]";
                
                if (!empty($cause)) {
                    $fired .= " caused by [{$cause}]";
                }
                
                $rtn[] = $fired;
            } else {
                if (empty($v['file']) === true) {
                    $args  = json_encode($v['object']);
                    $rtn[] = "\t#{$n}  {$v['function']}() called at [{$v['function']}:{$v['class']}] args by {$args}";
                } else {
                    $args  = json_encode($v['args']);
                    $rtn[] = "\t#{$n}  {$v['function']}() called at [{$v['file']}:{$v['line']}] args by {$args}";
                }
            }
            
        }
        
        $message = PHP_EOL . implode(PHP_EOL, $rtn);
        
        self::log($message);
    }
}