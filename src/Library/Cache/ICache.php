<?php
namespace Library\Cache;

/**
 * @author beanfondue@gmail.com
 *
 */
interface ICache
{
    const TYPE_GET = 1;
    const TYPE_SET = 2;
    const TYPE_DEL = 3;
    
    public function queue($arg);
    public function qexec($transaction = false, $binds = []);
    public function mexec($args, $transaction = false);
    public function hasQueue();
}