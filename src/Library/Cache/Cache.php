<?php
namespace Library\Cache;

use Exception;
use Common\Debug;
use Common\Packet;

use Library\Cache\ICache;

/**
 * @author beanfondue@gmail.com
 *
 */
class Cache implements ICache
{
    private $object;
    
    /**
     * @param array $args [kind, host, port, user, pass, name, wait]
     * @see         Should be replaced file at first time before running application
     *              from likes EnvironmentDetector or PreLoader.
     *              that is this->object assignment from usable library in __construct
     *              PHP is interpreter
     */
    public function __construct($args = [])
    {
        try {
            $this->object = new \Library\Cache\ERedis($args); // could be modificated
        } catch (Exception $e) {
            Debug::trace($e->getCode() . ': ' . $e->getMessage());
            
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_CACHE);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'An ' . __CLASS__ . ' error has occurred');
        }
    }
    
    public function queue($arg)
    {
        return $this->object->queue($arg);
    }
    
    public function qexec($transaction = false, $binds = [])
    {
        return $this->object->qexec($transaction, $binds);
    }
    
    public function mexec($args, $transaction = false)
    {
        return $this->object->mexec($args, $transaction);
    }
    
    public function hasQueue()
    {
        return $this->object->hasQueue();
    }
    
    public function loaded()
    {
        return $this->object;
    }
}