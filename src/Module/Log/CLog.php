<?php
namespace Module\Log;

use Common\Data;
use Common\Util;

/**
 * Current status is pseudocode
 * 
 * @author beanfondue@gmail.com
 *
 */
class CLog extends Data
{
    protected $label = 'log';
    
    private function async($data)
    {
        $rtn = true;
        
        // send query to external server (use fsockopen / curl / ...)
        
        return $rtn;
    }
    
    private function sync($data)
    {
        $rtn = true;
        
        // each or one query
        // $db = $this->db($this->label);
        // if ($db !== null) { ...
        
        return $rtn;
    }
    
    private function save($data, $async)
    {
        $rtn = true;
        
        if ($async === false) {
            $rtn = $this->sync($data);
        } else {
            $rtn = $this->async($data);
        }
        
        return $rtn;
    }
    
    public function crash($async = false)
    {
        // values only from client
        $data = 'INSERT INTO log_db.tb_crash (...) VALUES (...)';
        
        return $this->save($data, $async);
    }
    
    /**
     * @todo wealth($asis, $tobe, $async = false) ?
     */
    public function wealth($tobe, $async = false)
    {
        // get as-is data
        // calculation between as-is / to-be data
        // ...
        $data = 'INSERT INTO log_db.tb_wealth (...) VALUES (...)';
        
        return $this->save($data, $async);
    }
    
    /**
     * @param  string  $data
     * @return boolean
     * @todo   sync -> async
     */
    public function packet($data)
    {
        $rtn = true;
        
        $file_name = FDEF_PATH_LOG . DIRECTORY_SEPARATOR . 'packet.log';
        if (file_exists(dirname($file_name)) === false) {
            mkdir(dirname($file_name), 0755, true);
        }
        
        // sample for past filename
        $past = date('Ymd', strtotime('-1 day'));
        $file_name_past = $file_name . '.' . $past;
        
        if (file_exists($file_name) === true && file_exists($file_name_past) === false) {
            // real past filename
            $past_real           = date('Ymd', filemtime($file_name));
            $file_name_past_real = $file_name . '.' . $past_real;
            
            // date('Ymd') !== $past_real : remove offset between timezone and localzone(filemtime)
            if (date('Ymd') !== $past_real && strcmp($file_name_past_real, $file_name_past)) {
                // if different from sample and real, create fake file
                touch($file_name_past);
                $file_name_past = $file_name_past_real;
            }
            rename($file_name, $file_name_past);
        }
        
        $data = '[' . Util::date() . ' ' . FDEF_DEFAULT_TIMEZONE. '][' . FDEF_THREAD_ID . '] ' . $data;
        
        $fp = fopen($file_name, 'a+');
        if ($fp !== false) {
            fwrite($fp, $data . PHP_EOL);
            $rtn = fclose($fp);
        }
        
        return $rtn;
    }
}