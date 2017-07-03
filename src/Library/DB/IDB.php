<?php
namespace Library\DB;

/**
 * @author beanfondue@gmail.com
 *
 */
interface IDB
{
    public function fnmap($sql);
    public function queue($sql, $refs = []);
    public function qexec($transaction = false, $binds = []);
    public function mexec($sql, $refs = [], $transaction = false);
    public function hasQueue();
    public function escape($field);
    public function quote($value, $type = null);
    public function query($sql, $mode = null);
}