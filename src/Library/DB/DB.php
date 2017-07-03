<?php
namespace Library\DB;

use Exception;
use Common\Debug;
use Common\Packet;

use Library\DB\IDB;

/**
 * @author beanfondue@gmail.com
 *
 */
class DB implements IDB
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
            $this->object = new \Library\DB\EPDO($args); // could be modificated
        } catch (Exception $e) {
            Debug::trace($e->getCode() . ': ' . $e->getMessage());
            
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_DB);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'An ' . __CLASS__ . ' error has occurred');
        }
    }
    
    public function fnmap($sql)
    {
        return $this->object->fnmap($sql);
    }
    
    public function queue($sql, $refs = [])
    {
        return $this->object->queue($sql, $refs);
    }
    
    public function qexec($transaction = false, $binds = [])
    {
        return $this->object->qexec($transaction, $binds);
    }
    
    public function mexec($sql, $refs = [], $transaction = false)
    {
        return $this->object->mexec($sql, $refs, $transaction);
    }
    
    public function hasQueue()
    {
        return $this->object->hasQueue();
    }
    
    public function escape($field)
    {
        return $this->object->escape($field);
    }
    
    public function quote($value, $type = null)
    {
        return $this->object->quote($value, $type);
    }
    
    public function query($sql, $mode = null)
    {
        return $this->object->query($sql, $mode);
    }
    
    public function loaded()
    {
        return $this->object;
    }
    
    public function escapes($fields)
    {
        foreach ($fields as $k => $field) {
            $fields[$k] = $this->object->escape($field);
        }
        
        return $fields;
    }
    
    public function quotes($values)
    {
        foreach ($values as $k => $value) {
            $values[$k] = $this->object->quote($value); // ($value, gettype($value)); ?
        }
        
        return $values;
    }
}