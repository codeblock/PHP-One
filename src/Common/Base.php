<?php
namespace Common;

/**
 * Base class
 *
 * @author beanfondue@gmail.com
 * @see    Base
 *           ├─ Data
 *           │    └─ Package\Foo\Bar ...
 *           └─ Package\Foo\Bar ...
 *
 */
class Base
{
    private static $instances = [];

    /**
     * Returns an instance of class
     *
     * @return $this
     */
    public static function instance($classname = null)
    {
        if ($classname === null) {
            $classname = static::class;
        }
        
        if (isset(self::$instances[$classname]) === false) {
            self::$instances[$classname] = new $classname();
        }
        
        return self::$instances[$classname];
    }
}