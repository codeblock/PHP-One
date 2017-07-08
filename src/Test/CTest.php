<?php
namespace Test;

use Common\Data;
use Common\Debug;
use Common\Util;

class CTest extends Data
{
    protected $label = 'user';
    
    /**
     * Warning : Do not use this phrases because normalized application flows and FDEF_DATA_WRITEONCE option
     *           Use for non-caching readonly data
     * @example direct db access
     * @see just only example.
     *      just for test.
     */
    public function exampleDB()
    {
        $db = $this->db($this->label);
        
        if ($db !== null) {
            $stmt = $db->query('SELECT VERSION() FROM DUAL');
            $rs   = $stmt->fetch();
            echo json_encode($rs) . PHP_EOL;
        }
    }
    
    public static function println($str = '')
    {
        $ln = PHP_EOL;
        if (FDEF_CLI === false) {
            $ln = '<br />';
        }
        
        print($str . $ln);
    }
    
    public static function printr($arr = [])
    {
        if (FDEF_CLI === false) {
            print('<pre>');
        }
        print_r($arr);
        if (FDEF_CLI === false) {
            print('</pre>');
        }
    }
    
    public static function debugLog($str = '')
    {
        self::println($str);
        Debug::log($str);
    }
    
    /**
     * need for performance test
     *
     * @param number $n
     * @return string
     */
    private function makeBinds($n = 1, $m = 1)
    {
        $rtn = null;
        
        $n = max(1, $n);
        
        switch ($m) {
            case 1: // 0.001
                $rtn = substr(str_repeat(',?', $n), 1);
                break;
            case 2: // 0.030 ~ 0.059
                $rtn = implode(',', array_pad([], $n, '?'));
                break;
        }
        
        return $rtn;
    }
    
    private function arrayShift($n = 1, $m = 1)
    {
        $rtn = [];
        
        $n = max(1, $n);
        
        $i = 0;
        while ($i++ < $n) {
            $rtn = [0, 'a=1', 2, 'b=[456]', 4, 'c=[1:{"a", "b"}, 2:{"c", "d"}]'];
            
            switch ($m) {
                case 1: // 0.
                    $rtn = array_slice($rtn, 1);
                    break;
                case 2: // 0.
                    array_splice($rtn, 0, 1);
                    break;
                case 3: // 0.
                    array_shift($rtn);
                    break;
            }
        }
        $rtn = json_encode($rtn);
        
        return $rtn;
    }
    
    private function stringEndsWith($n = 1, $m = 1)
    {
        $rtn = false;
        
        $n = max(1, $n);
        
        $haystack = 'abcdefg';
        //$needle   = 'ef';
        //$needle   = 'xgfdsdfgx';
        //$needle   = null; // PHP Warning:  strpos(): Empty needle
        //$needle   = false; // PHP Warning:  strpos(): Empty needle
        //$needle   = true;
        //$needle   = 'g';
        $needle   = 'defg';
        
        $i = 0;
        while ($i++ < $n) {
            switch ($m) {
                case 1:
                    $needle_rev   = strrev($needle);
                    $haystack_rev = strrev($haystack);
                    $rtn = (strpos($haystack_rev, $needle_rev) === 0);
                    break;
                case 2:
                    $rpos = strrpos($haystack, $needle);
                    $rtn  = (substr($haystack, $rpos) === $needle);
                    break;
            }
        }
        var_dump($rtn);
        
        return $rtn;
    }
    
    private function utils($n = 1, $m = 1)
    {
        $rtn = null;
        
        $n = max(1, $n);
        
        //$param = 85497;
        //$param = '23:44:57';
        $param = '29:16:02';
        
        $i = 0;
        while ($i++ < $n) {
            switch ($m) {
                case 1:
                    $rtn = 1;
                    break;
                /*case 2:
                    $rtn = Util::hms2sec2($param);
                    break;
                case 3:
                    $rtn = Util::hms2sec3($param);
                    break;*/
            }
        }
        echo $rtn . PHP_EOL;
        
        return $rtn;
    }
    
    /**
     * @param int $n count for test
     * @param int $m method for test
     * @return void
     */
    public function performance($n, $m)
    {
        $precision = 5;
        $elapsed = microtime(true);
        
        //$this->makeBinds($n, $m);
        //$this->arrayShift($n, $m);
        //$this->stringEndsWith($n, $m);
        $this->utils($n, $m);
        
        $elapsed = number_format(microtime(true) - $elapsed, $precision);
        self::debugLog($elapsed);
    }
    
    public function testModule()
    {
        $user      = \Module\User\CUser::instance();
        $character = \Module\Character\CCharacter::instance();
        $version   = \Module\Version\CVersion::instance();
        
        $user->testMulti();
    }
    
    public function arrayManipulation()
    {
        echo __METHOD__ . PHP_EOL;
    }
    
    public function dateManipulation()
    {
        $a = '2017-07-02 01:02:03';
        $b = '2016-07-02 11:27:38';
        $z = Util::date();
        
        echo $a . PHP_EOL;
        echo $b . PHP_EOL;
        echo $z . PHP_EOL;
        
        $a = Util::timeFromString($a);
        $b = Util::timeFromString($b);
        $z = Util::timeFromString($z);
        
        echo Util::date('Y-m-d H:i:s', $a) . PHP_EOL;
        echo Util::date('Y-m-d H:i:s', $b) . PHP_EOL;
        echo Util::date('Y-m-d H:i:s', $z) . PHP_EOL;
    }
    
    public function rand()
    {
        $arr = [
            'x' => 76,
            'c' => 13,
            'a' => 1,
            'r' => 208,
            'n' => 87,
            'z' => 1000
        ];
        
        $rtn = Util::randByRangeOfSum($arr);
        
        echo $rtn . PHP_EOL;
    }
    
    public function test()
    {
        //$this->performance(1, 1);
        //$this->performance(100000, 1);
        
        //$this->testModule();
        //$this->exampleDB();
        //$this->arrayManipulation();
        //$this->dateManipulation();
        $this->rand();
    }
}