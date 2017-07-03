<?php
namespace Common;

use Module\Log\CLog;

/**
 * Works for I/O
 *
 * @author beanfondue@gmail.com
 *
 */
class Packet
{
    private static $instance;

    private static $req;
    private static $res;
    
    /**
     * Output when FDEF_DATA_WRITEONCE = true
     */
    public function __destruct()
    {
        self::_send();
    }
    
    private static function _send()
    {
        echo self::pack(self::$res);
        
        CLog::instance()->packet('[send] ' . json_encode(self::$res));
    }
    
    protected static function pack($data)
    {
        $data = json_encode($data);
        // $data = additional-encrypt-manipulation($data);
        $data = base64_encode($data);
        
        return $data;
    }

    protected static function unpack($data)
    {
        $data = base64_decode($data);
        // $data = additional-decrypt-manipulation($data);
        $data = json_decode($data, true);
        
        return $data;
    }

    public static function recv()
    {
        $rtn = true;
        
        // parsing
        if (self::$req === null) {
            self::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_NONE);
            self::set(FDEF_PACKET_RES_ERRTEXT, '');
            
            self::$req = [];
            
            $request = null;
            
            if (FDEF_CLI === true) {
                if ($_SERVER['argc'] > 1) {
                    $argv = $_SERVER['argv'];
                    
                    // remove executed filename
                    array_shift($argv);
                    
                    $request = [];
                    foreach ($argv as $arg) {
                        $k = strtok($arg, '=');
                        $v = strtok('');
                        if ($v === false) {
                            $v = null;
                        }
                        $request[$k] = $v;
                    }
                }
            } else {
                $request = $_REQUEST;
            }
            
            if (empty(FDEF_PACKET_REQ_KEY) === false && isset($request[FDEF_PACKET_REQ_KEY]) === true) {
                $request = $request[FDEF_PACKET_REQ_KEY];
                $request = self::unpack($request);
            }// else {
            //    $rtn = false;
            //}
            
            self::$req = $request;
            
            CLog::instance()->packet('[recv] ' . json_encode(self::$req));
        }
        
        return $rtn;
    }

    public static function send()
    {
        if (FDEF_DATA_WRITEONCE === true) {
            if (self::$instance === null) {
                self::$instance = new self();
            }
        } else {
            self::_send();
        }
    }

    /**
     * get the request data
     * 
     * @param string $k (nullable)
     * @return mixed
     */
    public static function get($k = null)
    {
        $rtn = null;
        
        self::recv();
        
        if ($k === null) {
            $rtn = self::$req;
        } else if (isset(self::$req[$k]) === true) {
            $rtn = self::$req[$k];
        }
        
        return $rtn;
    }
    
    /**
     * set the response data
     * 
     * @param string   $k      key
     * @param mixed    $v      value of key
     * @param boolean  $append mode for set or append
     * @return boolean
     */
    public static function set($k, $v)
    {
        $rtn = true;
        
        if (self::$res === null) {
            self::$res = [];
        }
        
        if (
               ($k === FDEF_PACKET_RES_ERRCODE && isset(self::$res[$k]) === true && self::$res[$k] !== FDEF_ERROR_NONE)
            || ($k === FDEF_PACKET_RES_ERRTEXT && isset(self::$res[$k]) === true && self::$res[$k] !== '')
        ) {
            $rtn = false;
        }
        
        if ($rtn === true) {
            self::$res[$k] = $v;
        }
        
        return $rtn;
    }
}