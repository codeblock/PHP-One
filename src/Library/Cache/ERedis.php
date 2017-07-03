<?php
namespace Library\Cache;

use Exception;
use Redis;
use RedisException;
use Common\Debug;
use Common\Packet;

use Library\Cache\ICache;

/**
 * @author beanfondue@gmail.com
 *
 */
class ERedis extends Redis implements ICache
{
    private $queue;
    
    public function __construct($args = [])
    {
        $rtn = null;
        
        $wait = 0.0;
        if (empty($args['wait']) === false) {
            $wait = $args['wait'];
        }
        
        try {
            if (FDEF_DATA_PCONNECT === true) {
                $rtn = parent::pconnect($args['host'], $args['port'], $wait);
            } else {
                $rtn = parent::connect($args['host'], $args['port'], $wait);
            }
            
            if ($rtn === true && isset($args['pass']) === true) {
                $rtn = parent::auth($args['pass']);
            }
            if ($rtn === true && isset($args['name']) === true) {
                $rtn = parent::select($args['name']);
            }
            
            if ($rtn === false) {
                $err = $this->getLastError();
                $this->clearLastError();
                
                throw new RedisException($err, FDEF_ERROR_CACHE);
            }
            
            // Debug::log('created');
        } catch (RedisException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        return $rtn;
    }
    
    public function __destruct()
    {
        $this->qexec();
        
        parent::close();
    }
    
    public function queue($arg)
    {
        if (empty($arg) === false) {
            if ($this->queue === null) {
                $this->queue = [];
            }
            
            $this->queue[] = $arg;
        }
        
        return $this;
    }
    
    public function qexec($transaction = false, $binds = [])
    {
        $rtn = true;
        $mnt = null;
        
        if ($this->queue !== null) {
            $that = $this;
            
            if ($transaction === true) {
                $that = $this->multi();
            }
            
            if (empty($binds) === false) {
                foreach ($this->queue as &$v) {
                    $newid_pos = strrpos($v['index'], FDEF_DATA_NEWIDPREFIX);
                    if ($newid_pos !== false) {
                        $index_head = substr($v['index'], 0, $newid_pos);
                        $newid      = substr($v['index'], $newid_pos);
                        $v['index'] = $index_head . $binds[$newid]; // without validation. it must be exists key for finds allways
                    }
                }
            }
            
            $mnt = $that->mexec($this->queue, $transaction);
            if ($mnt === false) {
                $rtn = false;
            }
            
            if ($transaction === true) {
                if ($rtn === false) {
                    $that->discard();
                } else {
                    $that->exec();
                }
            }
            
            $this->queue = null;
        }
        
        //var_dump($mnt);
        //var_dump($rtn);
        return $rtn;
    }
    
    /**
     * {@inheritDoc}
     * @see
     *      $transaction = false : multi() can called
     *      $transaction = true  : multi() wouldn't be called (already started multi() in caller)
     */
    public function mexec($args, $transaction = false)
    {
        $rtn = null;
        
        $that = $this;
        
        if (key($args) === 'types') {
            $v = $args;
            switch ($v['types']) {
                case self::TYPE_GET:
                    if (empty($v['value']) === true) {
                        $rtn = $that->hGetAll($v['index']);
                    } else {
                        $rtn = $that->hMGet($v['index'], $v['value']);
                    }
                    break;
                case self::TYPE_SET:
                    $rtn = $that->hMSet($v['index'], $v['value']);
                    if (FDEF_DATA_CACHETTL > 0) {
                        $that->expire($v['index'], FDEF_DATA_CACHETTL);
                    }
                    break;
                case self::TYPE_DEL:
                    $rtn = $that->del($v['index']);
                    break;
            }
        } else {
            if ($transaction === false) {
                $that = $this->multi();
            }
            
            foreach ($args as $v) {
                switch ($v['types']) {
                    case self::TYPE_GET:
                        if (empty($v['value']) === true) {
                            $that->hGetAll($v['index']);
                        } else {
                            $that->hMGet($v['index'], $v['value']);
                        }
                        break;
                    case self::TYPE_SET:
                        $that->hMSet($v['index'], $v['value']);
                        if (FDEF_DATA_CACHETTL > 0) {
                            $that->expire($v['index'], FDEF_DATA_CACHETTL);
                        }
                        break;
                    case self::TYPE_DEL:
                        $that->del($v['index']);
                        break;
                }
            }
            
            if ($transaction === false) {
                $rtn = $that->exec();
            }
        }
        
        $errors = $that->getLastError();
        if (empty($errors) === false) {
            $rtn = false;
            
            if ($transaction === false && key($args) === 'types') {
                $that->discard();
            }
            $that->clearLastError();
            
            Debug::trace(json_encode($errors));
            
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_CACHE);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'an error has occurred');
        }
        
        return $rtn;
    }
    
    public function hasQueue()
    {
        return $this->queue !== null;
    }
}