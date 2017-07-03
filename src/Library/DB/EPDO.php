<?php
namespace Library\DB;

use Exception;
use PDO;
use PDOException;
use Common\Debug;
use Common\Packet;

use Library\DB\IDB;

/**
 * @author beanfondue@gmail.com
 *
 */
class EPDO extends PDO implements IDB
{
    private $kind;
    private $refs;
    private $queue;
    
    /**
     * @param array $args [kind, host, port, user, pass, name, wait]
     */
    public function __construct($args = [])
    {
        $rtn = null;
        
        $this->kind = $args['kind'];
        
        $dsn = '';
        $dsn .= $args['kind'] . ':';
        $dsn .= 'host=' . $args['host'] . ';';
        $dsn .= 'port=' . $args['port'] . ';';
        if (empty($args['name']) === false) {
            $dsn .= 'dbname=' . $args['name'] . ';';
        }
        
        $opts = [
            //PDO::ATTR_EMULATE_PREPARES => false
        ];
        if (isset($args['wait']) === true) {
            $opts[PDO::ATTR_TIMEOUT] = $args['wait'];
        }
        if (FDEF_DATA_PCONNECT === true) {
            $opts[PDO::ATTR_PERSISTENT] = true;
        }
        
        try {
            $rtn = parent::__construct($dsn, $args['user'], $args['pass'], $opts);
            
            // Debug::log('created');
        } catch (PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        return $rtn;
    }
    
    public function __destruct()
    {
        $this->qexec();
        
        // close: http://php.net/manual/en/pdo.connections.php
    }
    
    public function fnmap($sql)
    {
        $fict = ['INCRX()', 'ROW_CONCAT('];
        $fact = $fict;
        
        if ($this->kind === 'mysql') {
            $fact = ['LAST_INSERT_ID()', 'GROUP_CONCAT('];
        } else if ($this->kind === 'oci') {
            // LAST_INSERT_ID() function must be implemented
            $fact = ['LAST_INSERT_ID()', 'WM_CONCAT('];
        }
        
        $rtn = str_replace($fict, $fact, $sql);
        
        return $rtn;
    }
    
    public function queue($sql, $refs = [])
    {
        if (empty($sql) === false) {
            if ($this->queue === null) {
                $this->queue = [];
            }
            
            $this->queue[] = $sql;
            
            if (empty($refs) === false) {
                if ($this->refs === null) {
                    $this->refs = [];
                }
                $this->refs = array_merge($this->refs, $refs);
            }
        }
        
        return $this;
    }
    
    /**
     * plain query only, cannot execute procedure.
     * return false on failure (use validation === false)
     *
     * @param boolean $transaction
     * @param array   $binds  []
     * @return mixed
     */
    public function qexec($transaction = false, $binds= [])
    {
        $rtn = true;
        $mnt = null;
        
        if ($this->queue !== null) {
            if ($transaction === true) {
                $this->beginTransaction();
            }
            
            $queries = implode(';', $this->queue);
            
            if (empty($binds) === false) {
                foreach ($binds as $k => $v) {
                    $v       = $this->quote($v);
                    $named   = array_pad([], count($v), '/:' . $k . '/');
                    $queries = preg_replace($named, $v, $queries, 1);
                }
            }
            
            $mnt = $this->mexec($queries, $this->refs, $transaction);
            if ($mnt === false) {
                $rtn = false;
            }
            
            if ($transaction === true) {
                if ($rtn === false) {
                    $this->rollBack();
                } else {
                    $this->commit();
                }
            }
            
            $this->refs  = null;
            $this->queue = null;
        }
        
        //var_dump($mnt);
        //var_dump($rtn);
        return $rtn;
    }
    
    /**
     * multi exec
     * return false on failure (validation : return value === false)
     *
     * @return array|boolean
     */
    public function mexec($sql, $refs = [], $transaction = false)
    {
        $rtn = null;
        
        $mnt  = [];
        
        $stmt = $this->query('CALL sp_multi_query("' . $sql . '", ' . (int)$transaction . ')');
        
        if ($stmt !== false) {
            do {
                if ($stmt->rowCount() > 0) {
                    $mnt = array_merge($mnt, $stmt->fetchAll());
                }
            } while ($stmt->nextRowSet() === true);
        }
        
        // ANSI SQL-92: SUCCESSFUL_COMPLETION_NO_SUBCLASS = "00000"
        if ($this->errorCode() === '00000') {
            $tmp = array_pop($mnt);
            
            $rtn = ['data' => $mnt, 'info' => $tmp];
            
            if (empty($refs) === false && empty($tmp['inserted']) === false) {
                $inserted = explode(',', $tmp['inserted']);
                foreach ($refs as $k => &$v) {
                    $v = (int)$inserted[$k];
                }
            }
        } else {
            $rtn = false;
            
            $errors = array_combine(['sqlstate', 'errno', 'error'], $this->errorInfo());
            Debug::trace(json_encode($errors));
            
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_DB);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'an error has occurred');
        }
        
        return $rtn;
    }
    
    public function hasQueue()
    {
        return $this->queue !== null;
    }
    
    public function escape($field)
    {
        $rtn = explode(' ', $field);
        
        $rtn[0] = '`' . $rtn[0]. '`';
        if (count($rtn) > 1) {
            $rtn[count($rtn) - 1] = '`' . $rtn[count($rtn) - 1]. '`';
        }
        $rtn = implode(' ', $rtn);
        
        return $rtn;
    }
    
    public function quote($value, $type = 'string')
    {
        if ($type === null && gettype($value) === 'string' || $type === 'string') {
            $value = parent::quote($value);
        }
        
        return $value;
    }
    
    public function query($sql, $mode = null)
    {
        if ($mode === null) {
            $mode = PDO::FETCH_ASSOC;
        }
        
        return parent::query($sql, $mode);
    }
}