<?php
namespace Common;

use Common\Base;
use Common\Util;
use Library\Cache\Cache;
use Library\DB\DB;

/**
 * Works for Data with write at once and auto Caching
 * output fields are $this->index + $this->field
 *
 * @author beanfondue@gmail.com
 * @see    Base
 *           ├─ Data
 *           │    └─ Package\Foo\Bar ...
 *           └─ Package\Foo\Bar ...
 *
 */
class Data extends Base
{
    /**
     * DB scope name
     * 
     * @var string
     */
    protected $label;
    
    /**
     * DB.Table
     * 
     * @var string
     */
    protected $table;
    
    /**
     * NEXTVAL / AUTO_INCREMENT / ...
     * 
     * @var string
     * @see it could be same with $this->index
     */
    protected $incrx;
    
    /**
     * DB.Table.PrimaryKey
     * 
     * @var string
     * @see single: 'PK', multiple: 'PK1,PK2, ...'
     */
    protected $index;
    
    /**
     * DB.Table.Fields
     * 
     * @var string
     * @see 'Field1,Field2, ...'
     */
    protected $field;
    
    
    /**
     * DB.Table.FieldTypes
     * 
     * @var array
     * @see sequences must be matched with ($this->index + $this->field)
     */
    protected $types;
    
    protected $field_delimiter = ',';
    
    protected $key_prefix;
    protected $key_suffix;
    protected $key_glue = '_';
    
    private static $buckets = [];
    private static $caches  = [];
    private static $dbs     = [];
    
    private static $newids  = [];
    private static $newid   = 0;
    
    public function __destruct()
    {
        if (FDEF_DATA_WRITEONCE === true) {
            $mnt = true;
            
            //if ($mnt === true) {
                foreach (self::$dbs as $v) {
                    if ($v->hasQueue() === true) {
                        $mnt = $v->qexec(FDEF_DATA_TRANSACTION);
                    }
                    if ($mnt === false) {
                        break;
                    }
                }
            //}
            
            if ($mnt === true) {
                foreach (self::$caches as $v) {
                    if ($v->hasQueue() === true) {
                        $mnt = $v->qexec(FDEF_DATA_TRANSACTION, self::$newids);
                    }
                    //if ($mnt === false) {
                    //    break;
                    //}
                }
            }
        }
    }
    
    private function getset(&$target, $label, $k = null, $v = null, $merge = false)
    {
        if (empty($target[$label]) === true) {
            $target[$label] = [];
        }
        $rtn = $target[$label];
        
        if ($k !== null) {
            if (isset($rtn[$k]) === true) {
                $rtn = $rtn[$k];
            } else {
                $rtn = null;
            }
        }
        
        if ($v !== null) {
            if ($k === null) {
                if ($merge === true) {
                    $target[$label]     = array_merge($target[$label], $v);
                } else {
                    $target[$label]     = $v;
                }
            } else {
                if (isset($target[$label][$k]) === false) {
                    $target[$label][$k] = [];
                }
                if ($merge === true) {
                    $target[$label][$k] = array_merge($target[$label][$k], $v);
                } else {
                    $target[$label][$k] = $v;
                }
            }
            $rtn = $v;
        }
        
        return $rtn;
    }
    
    /**
     * internal bucket
     * 
     * @param string $label
     * @param string $k
     * @param mixed  $v
     * @return NULL|mixed
     */
    private function bucket($label, $k = null, $v = null, $merge = false)
    {
        return $this->getset(self::$buckets, $label, $k, $v, $merge);
    }
    
    private function newid($k = null, $v = false)
    {
        if ($k === null) {
            $n = ++self::$newid;
            $k = FDEF_DATA_NEWIDPREFIX . $n;
            $v = $k;
        }
        
        return $this->getset(self::$newids, $k, null, $v);
    }
    
    private function match($search, $result)
    {
        $rtn = false;
        
        if (is_array($search) === true) {
            $comp = array_filter($result);
            if (count($search) === count($comp)) {
                $rtn = true;
            }
        } else {
            if (empty($result) === false) {
                $rtn = true;
            }
        }
        
        return $rtn;
    }
    
    /**
     * type casting for value
     * 
     * @param unknown $v
     * @param int $type
     * @return void
     */
    private function settype(&$v, $type)
    {
        if (strpos($v, FDEF_DATA_NEWIDPREFIX) !== 0) {
            switch ($type) {
                case FDEF_TYPE_NULL:
                    break;
                case FDEF_TYPE_BOOL:
                    $v = (bool)$v;
                    break;
                case FDEF_TYPE_INTEGER:
                    $v = (int)$v;
                    break;
                case FDEF_TYPE_FLOAT:
                    $v = (float)$v;
                    break;
                case FDEF_TYPE_DOUBLE:
                    $v = (double)$v;
                    break;
                case FDEF_TYPE_CHAR:
                case FDEF_TYPE_STRING:
                case FDEF_TYPE_DATE:
                    $v = (string)$v;
                    break;
            }
        }
    }
    
    /**
     * type casting for map
     * 
     * @param mixed $map
     * @return void
     */
    private function settypes(&$map)
    {
        $fields = $this->fieldExplode($this->fields());
        
        foreach ($map as $k => &$v) {
            $idx = array_search($k, $fields);
            $type = $this->types[$idx];
            $this->settype($v, $type);
        }
        
        reset($map);
    }
    
    /**
     * Cache Object
     * 
     * @param string $label
     * @return NULL|Cache
     * @todo protected -> private
     */
    protected function cache($label)
    {
        $rtn = null;
        
        if (empty(self::$caches[$label]) === true && empty(FDEF_CACHE_CONN[$label]) === false) {
            $load = new Cache(FDEF_CACHE_CONN[$label]);
            if ($load->loaded() !== null) {
                self::$caches[$label]= $load;
            }
        }
        if (empty(self::$caches[$label]) === false) {
            $rtn = self::$caches[$label];
        }
        
        return $rtn;
    }
    
    /**
     * DB Object
     * 
     * @param string $label
     * @return NULL|DB
     * @todo protected -> private
     */
    protected function db($label)
    {
        $rtn = null;
        
        if (empty(self::$dbs[$label]) === true && empty(FDEF_DB_CONN[$label]) === false) {
            $load = new DB(FDEF_DB_CONN[$label]);
            if ($load->loaded() !== null) {
                self::$dbs[$label] = $load;
            }
        }
        if (empty(self::$dbs[$label]) === false) {
            $rtn = self::$dbs[$label];
        }
        
        return $rtn;
    }
    
    /**
     * creating data structure of $this->table
     * 
     * @param unknown $k
     * @return mixed[]
     */
    protected function creator($k)
    {
        $rtn = [];
        $mnt = $this->fieldExplode($this->fields());
        
        // remove alias expressions if exists. only fields for DB
        foreach ($mnt as $v) {
            $v     = strtok($v, ' ');
            $value = null;
            if ($v === $this->incrx) {
                $value   = $this->newid();
                $rtn[$v] = &self::$newids[$value];
            } else {
                $rtn[$v] = $value;
            }
        }
        
        return $rtn;
    }
    
    protected function fields()
    {
        return $this->index . $this->field_delimiter . $this->field;
    }
    
    protected function fieldExplode($str)
    {
        return explode($this->field_delimiter, $str);
    }
    
    /**
     * is complexed PK ?
     * 
     * @return boolean
     */
    protected function indexComplexed()
    {
        if (strpos($this->index, $this->field_delimiter) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * value of $this->index when index is complexed
     * 
     * @param array $arr
     * @return NULL|string
     */
    protected function valueImplodeByIndex($arr)
    {
        $rtn = null;
        
        $index = $this->fieldExplode($this->index);
        foreach ($index as $v) {
            if (isset($arr[$v]) === true) {
                $rtn .= $this->field_delimiter . $arr[$v];
            }
        }
        
        if ($rtn !== null) {
            $rtn = substr($rtn, strlen($this->field_delimiter));
        }
        
        return $rtn;
    }
    
    /**
     * value of cache key
     * 
     * @param string $k
     * @return string
     */
    protected function key($k)
    {
        $k = $this->table . $this->key_glue . $k;
        
        return $this->key_prefix . $k . $this->key_suffix;
    }
    
    /**
     * getter: row
     * 
     * @param mixed([])  $keys
     * @param boolean    $caching
     * @return           array[]
     */
    protected function get($keys, $caching = true)
    {
        $label = $this->label;
        
        $rtn = $this->read($label, $keys, $caching);
        
        if (isset($rtn['data']) === true) {
            if (Util::isRows($rtn['data']) === true) {
                // [[0], [1], ...] -> [0]
                $rtn = current($rtn['data']);
            } else {
                // []
                $rtn = $rtn['data'];
            }
        }
        
        return $rtn;
    }
    
    /**
     * getter: rows
     * 
     * @param mixed([])  $keys
     * @param boolean    $caching
     * @return           array[]|array[][]
     */
    protected function gets($keys, $caching = true)
    {
        $label = $this->label;
        
        $rtn = $this->read($label, $keys, $caching);
        
        if (isset($rtn['data']) === true) {
            $rtn = $rtn['data'];
        }
        
        return $rtn;
    }
    
    /**
     * setter: row[s]
     * 
     * @param mixed([])  $keys
     * @param array([])  $vals
     * @param boolean    $caching
     * @return boolean
     */
    protected function set($keys, $vals, $caching = true)
    {
        $label = $this->label;
        
        return $this->write($label, $keys, $vals, $caching);
    }
    
    /**
     * delete cache
     * 
     * @param mixed([])  $keys
     * @param boolean    $caching
     * @return boolean
     */
    protected function reload($keys, $caching = true)
    {
        $label = $this->label;
        
        return $this->delete($label, $keys, $caching);
    }
    
    /**
     * @param string     $label
     * @param mixed([])  $keys
     * @param boolean    $caching
     * @return mixed     ['data' => $data, 'info' => $info] or false
     */
    private function read($label, $keys, $caching = true)
    {
        $rtn = null;
        
        if ($rtn === null) {
            $bucket = $this->bucket($label);
            $mnt = null;
            
            if (is_array($keys) === true) {
                foreach ($keys as $each_k) {
                    $cachekey = $this->key($each_k);
                    if (isset($bucket[$cachekey]) === true) {
                        if ($mnt === null) {
                            $mnt = ['data' => [], 'info' => []];
                        }
                        $mnt['data'][] = $bucket[$cachekey];
                    }
                }
            } else {
                $cachekey = $this->key($keys);
                if (isset($bucket[$cachekey]) === true) {
                    $mnt = ['data' => [], 'info' => []];
                    $mnt['data'][] = $bucket[$cachekey];
                }
            }
            
            if (isset($mnt['data']) === true) {
                // same count filter
                if ($this->match($keys, $mnt['data']) === true) {
                    $rtn = $mnt;
                }
            }
        }
        
        // It would be called when new data before write()
        // validation for temporary data that is inserted after a while
        // it is not real data. should not be queried.
        if ($rtn === null) {
            if (is_array($keys) === true) {
                foreach ($keys as $k => $k_each) {
                    $newidpos = strrpos($k_each, FDEF_DATA_NEWIDPREFIX);
                    if ($newidpos !== false) {
                        unset($keys[$k]);
                    }
                }
            } else {
                $newidpos = strrpos($keys, FDEF_DATA_NEWIDPREFIX);
                if ($newidpos !== false) {
                    $keys = null;
                }
            }
            if (empty($keys) === true) {
                $rtn = ['data' => [[]], 'info' => []];
                return $rtn;
            }
        }
        
        $cache = null;
        if ($rtn === null && FDEF_DATA_CACHEUSE === true && $caching === true) {
            $cache = $this->cache($label);
        }
        
        if ($rtn === null && FDEF_DATA_CACHEUSE === true && $caching === true && $cache !== null) {
            $params = null;
            
            if (is_array($keys) === true) {
                foreach ($keys as $each_k) {
                    if ($params === null) {
                        $params = [];
                    }
                    $params[] = [
                        'types' => Cache::TYPE_GET,
                        'table' => $this->table,
                        'index' => $this->key($each_k),
                        'value' => []
                    ];
                }
            } else {
                $params = [
                    'types' => Cache::TYPE_GET,
                    'table' => $this->table,
                    'index' => $this->key($keys),
                    'value' => []
                ];
            }
            
            $mnt = [];
            if ($params !== null) {
                $mnt = $cache->mexec($params);
            }
            
            // same count filter
            if ($this->match($keys, $mnt) === false) {
                $mnt = false;
            }
            
            //if ($mnt !== false) {
            if (empty($mnt) === false) {
                if (is_array($keys) === false) {
                    $mnt = [$mnt]; // row:[] to rows:[[]]
                }
                
                $rtn = ['data' => $mnt, 'info' => []];
                
                foreach ($rtn['data'] as &$value) {
                    $valuek   = $this->valueImplodeByIndex($value);
                    if ($valuek === null) {
                        continue;
                    }
                    $cachekey = $this->key($valuek);
                    
                    $this->settypes($value);
                    $this->bucket($label, $cachekey, $value);
                }
                
                reset($rtn['data']);
            }
        }
        
        $db = null;
        if ($rtn === null) {
            $db = $this->db($label);
        }
        
        if ($rtn === null && $db !== null) {
            $rtn = [];
            
            $where = '';
            if (is_array($keys) === true) {
                if ($this->indexComplexed() === true) {
                    // complex index: [(f1 = v1 AND f2 = v2 AND fn = vn ...) OR (f1 = v11 AND f2 = v21 AND fn = vn1 ...) OR ...]
                    $indexk = $this->fieldExplode($this->index);
                    foreach ($keys as $each_k) {
                        $indexv = $this->fieldExplode($each_k);
                        
                        foreach ($indexk as $xk) {
                            $xv     = current($indexv);
                            if ($xv === false) {
                                break;
                            }
                            $where .= ' AND ' . $xk . ' = ' . $db->quote($xv);
                            next($indexv);
                        }
                        $where = ' OR (' . substr($where, 5) . ')';
                    }
                    $where = substr($where, 4);
                } else {
                    // simple index: [f1 IN (v1, v2, vn ...)]
                    $idxs = null;
                    foreach ($keys as $each_k) {
                        $idxs .= ',' . $db->quote($each_k);
                    }
                    $where = $this->index . ' IN (' . substr($idxs, 1) . ')';
                }
            } else {
                if ($this->indexComplexed() === true) {
                    // complex index: [f1 = v1 AND f2 = v2 AND fn = vn ...]
                    $indexk = $this->fieldExplode($this->index);
                    $indexv = $this->fieldExplode($keys);
                    
                    foreach ($indexk as $xk) {
                        $xv     = current($indexv);
                        if ($xv === false) {
                            break;
                        }
                        $where .= ' AND ' . $xk . ' = ' . $db->quote($xv);
                        next($indexv);
                    }
                    $where = substr($where, 5);
                } else {
                    // simple index: [f1 = v1]
                    $where = $this->index . ' = ' . $db->quote($keys);
                }
            }
            
            $fields = $this->fields();
            if ($this->field_delimiter !== ',') {
                $fields = str_replace($this->field_delimiter, ',', $fields);
            }
            $fields = $db->fnmap($fields);
            $param  = 'SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ' . $where;
            $rtn    = $db->mexec($param);
            
            if (empty($rtn['data']) === false && FDEF_DATA_CACHEUSE === true && $caching === true) {
                $cache = $this->cache($label);
                
                $params = [];
                foreach ($rtn['data'] as $value) {
                    $valuek   = $this->valueImplodeByIndex($value);
                    if ($valuek === null) {
                        continue;
                    }
                    $cachekey = $this->key($valuek);
                    $param    = [
                        'types' => Cache::TYPE_SET,
                        'table' => $this->table,
                        'index' => $cachekey,
                        'value' => $value
                    ];
                    
                    if (FDEF_DATA_WRITEONCE === true) {
                        $cache->queue($param); // once mode
                    }
                    
                    $params[] = $param;
                }
                
                if (FDEF_DATA_WRITEONCE === false && count($params) > 0) {
                    $cache->mexec($params); // each mode
                }
            }
            
            if (empty($rtn['data']) === false) {
                foreach ($rtn['data'] as &$value) {
                    $valuek   = $this->valueImplodeByIndex($value);
                    if ($valuek === null) {
                        continue;
                    }
                    $cachekey = $this->key($valuek);
                    
                    $this->settypes($value);
                    $this->bucket($label, $cachekey, $value);
                }
                
                reset($rtn['data']);
            }
        }
        
        return $rtn;
    }
    
    /**
     * @param string     $label
     * @param mixed([])  $keys
     * @param array([])  $vals
     * @param boolean    $caching
     * @return boolean
     */
    private function write($label, $keys, $vals, $caching = true)
    {
        $rtn = true;
        
        $db = null;
        
        if ($this->match($keys, $vals) === false) {
            $rtn = false;
        } else {
            $db = $this->db($label);
        }
        
        if ($rtn === true && $db !== null) {
            $sqls      = [];
            $incr      = [];
            $asises    = [];
            $reads     = $this->read($label, $keys, $caching);
            if (isset($reads['data']) === true) {
                $asises = $reads['data'];
            }
            
            if (is_array($keys) === false) {
                $keys = [$keys];
                $vals = [$vals];
            }
            
            $maps = array_combine($keys, $vals);
            $maps = Util::arrayRemovesByValues($maps, [null]);
            
            $mnt = true;
            
            foreach ($maps as $k => $v) {
                $mode = 0; // 0: insert, 1: update
                $asis = current($asises);
                $tobe = Util::arrayMergeByKey($asis, $v); // all
                $sql  = null;
                
                next($asises);
                
                if (empty($tobe) === true) {
                    $tobe = $v;
                } else {
                    $mode = 1;
                    $tobe = array_diff_assoc($tobe, $asis); // changes only
                }
                
                if ($mode === 1) {
                    // update
                    if (count($tobe) === 0) {
                        $tobe = null; // for Util::arrayRemoveByValues
                    } else {
                        $fields = '';
                        foreach ($tobe as $each_k => $each_v) {
                            //$fields .= ',' . $db->escape($each_k) . ' = ' . $db->quote($each_v);
                            $fields .= ',' . $each_k . ' = ' . $db->quote($each_v);
                        }
                        $fields = substr($fields, 1);
                        
                        $where = '';
                        if ($this->indexComplexed() === true) {
                            $indexk = $this->fieldExplode($this->index);
                            $indexv = $this->fieldExplode($k);
                            
                            foreach ($indexk as $xk) {
                                $xv     = current($indexv);
                                if ($xv === false) {
                                    break;
                                }
                                $where .= ' AND ' . $xk . ' = ' . $db->quote($xv);
                                next($indexv);
                            }
                            $where = substr($where, 5);
                        } else {
                            if (FDEF_DATA_WRITEONCE === true && strpos($k, FDEF_DATA_NEWIDPREFIX) === 0) {
                                $where = $this->index . ' = INCRX()';
                                $where = $db->fnmap($where);
                            } else {
                                $where = $this->index . ' = ' . $db->quote($k);
                            }
                        }
                        $sql = 'UPDATE ' . $this->table . ' SET ' . $fields . ' WHERE ' . $where;
                    }
                } else {
                    // insert
                    $fields = '';
                    $values = '';
                    foreach ($tobe as $each_k => $each_v) {
                        if ($each_k === $this->incrx && Util::stringStartsWith($each_v, FDEF_DATA_NEWIDPREFIX) === true) {
                            // soft NEXTVAL / AUTO_INCREMENT
                            $tobe[$each_k]    = &self::$newids[$each_v];
                            $incr[]           = &self::$newids[$each_v];
                            continue;
                        }
                        //$fields .= ',' . $db->escape($each_k);
                        $fields .= ',' . $each_k;
                        $values .= ',' . $db->quote($each_v);
                    }
                    $fields = substr($fields, 1);
                    $values = substr($values, 1);
                    
                    $sql = 'INSERT INTO ' . $this->table . ' (' . $fields . ') VALUES (' . $values . ')';
                }
                
                if ($sql !== null) {
                    if (FDEF_DATA_WRITEONCE === true) {
                        $db->queue($sql, array_slice($incr, -1)); // once mode
                    } else {
                        $sqls[] = $sql;
                    }
                }
                
                // prepare the cache data by only defined fields
                if ($mode === 0) {
                    $cols = $this->fieldExplode($this->fields());
                    $cols = array_fill_keys($cols, null);
                    $tobe = Util::arrayMergeByKey($cols, $tobe);
                }
                
                $maps[$k] = $tobe;
            }
            
            $maps = Util::arrayRemoveByValues($maps, [null]);
            
            if (FDEF_DATA_WRITEONCE === false && count($sqls) > 0) { // each mode
                $sqls = implode(';', $sqls);
                $mnt  = $db->mexec($sqls, $incr);
                
                if ($mnt === false) {
                    $rtn = false;
                }
            }
            
            $cache = null;
            if ($rtn === true && FDEF_DATA_CACHEUSE === true && $caching === true) {
                $cache = $this->cache($label);
            }
            
            if ($rtn === true && FDEF_DATA_CACHEUSE === true && $caching === true && $cache !== null) {
                $params = [];
                
                foreach ($maps as $k => $v) {
                    if ($this->incrx !== null && isset($v[$this->incrx]) === true) {
                        // $v[$this->incrx] is ...
                        // non-queue-mode: 100000000
                        //     queue-mode: @insertid${n}
                        // when update mode, $k is already real value.
                        $k = $v[$this->incrx];
                    }
                    $cachekey = $this->key($k);
                    
                    $param = [
                        'types' => Cache::TYPE_SET,
                        'table' => $this->table,
                        'index' => $cachekey,
                        'value' => $v
                    ];
                    
                    if (FDEF_DATA_WRITEONCE === true) {
                        $cache->queue($param); // once mode
                    } else {
                        $params[] = $param;
                    }
                }
                
                if (FDEF_DATA_WRITEONCE === false && count($params) > 0) { // each mode
                    $mnt = $cache->mexec($params);
                    if ($mnt === false) {
                        $rtn = false;
                    }
                }
            }
            
            if ($rtn === true) {
                foreach ($maps as $k => $v) {
                    if ($this->incrx !== null && isset($v[$this->incrx]) === true) {
                        // $v[$this->incrx] is ...
                        // non-queue-mode: 100000000
                        //     queue-mode: @insertid${n}
                        // when update mode, $k is already real value.
                        $k = $v[$this->incrx];
                    }
                    // but, caller( extends Data) doesn't know updated search key.

                    $cachekey = $this->key($k);
                    
                    $this->settypes($v);
                    $this->bucket($label, $cachekey, $v, true);
                }
            }
        }
        
        return $rtn;
    }
    
    /**
     * @param string     $label
     * @param mixed([])  $keys
     * @param boolean    $caching
     * @return boolean
     */
    private function delete($label, $keys, $caching = true)
    {
        $rtn = true;
        
        if (is_array($keys) === false) {
            $keys = [$keys];
        }
        
        $cache = null;
        if (FDEF_DATA_CACHEUSE === true && $caching === true) {
            $cache = $this->cache($label);
            if ($cache === null) {
                $rtn = false;
            }
        }
        
        if ($rtn === true) {
            $params = [];
            foreach ($keys as $k) {
                $cachekey = $this->key($k);
                
                if ($cache !== null) {
                    $param = [
                        'types' => Cache::TYPE_DEL,
                        'table' => $this->table,
                        'index' => $cachekey,
                        'value' => []
                    ];
                    
                    if (FDEF_DATA_WRITEONCE === true) {
                        $cache->queue($param); // once mode
                    } else {
                        $params[] = $param;
                    }
                }
                
                if (isset(self::$buckets[$label][$cachekey]) === true) {
                    unset(self::$buckets[$label][$cachekey]);
                }
            }
            
            if ($cache !== null && FDEF_DATA_WRITEONCE === false && count($params) > 0) { // each mode
                $mnt = $cache->mexec($params);
                if ($mnt === false) {
                    $rtn = false;
                }
            }
        }
        
        return $rtn;
    }
}