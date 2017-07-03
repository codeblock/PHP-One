<?php
namespace Module\User;

use Common\Data;
use Common\Debug;
use Common\Packet;
use Common\Util;
use Module\User\CUnique;

class CUser extends Data
{
    protected $label = 'user';
    
    protected $table = 'tb_user';
    protected $incrx = 'seq';
    protected $index = 'seq';
    protected $field = 'id,cash,cash_free,date_login,date_logout';
    protected $types = [
        FDEF_TYPE_INTEGER, // seq
        FDEF_TYPE_STRING,  // id
        FDEF_TYPE_INTEGER, // cash
        FDEF_TYPE_INTEGER, // cash_free
        FDEF_TYPE_DATE,    // date_login
        FDEF_TYPE_DATE     // date_logout
    ];
    
    protected function creator($k)
    {
        $rtn = parent::creator($k);
        
        $now = Util::date();
        
        $rtn['id']          = $k;
        $rtn['cash']        = 0;
        $rtn['cash_free']   = 2000;
        $rtn['date_login']  = $now;
        $rtn['date_create'] = $now;
        
        return $rtn;
    }
    
    private function create($k)
    {
        $rtn = null;
        
        $params = $this->creator($k);
        
        $mnt = $this->set($params[$this->index], $params);
        if ($mnt === true) {
            $rtn = $this->get($params[$this->index]);
        }
        
        return $rtn;
    }
    
    public function login()
    {
        $rtn = true;
        
        $mnt = null;
        
        $params = null;
        $param  = null;
        $seq    = (int)Packet::get('useq');
        $id     = Packet::get('uid');
        $now    = Util::date();
        
        // 1. search by Primary Key (caching)
        if ($seq > 0) {
            $param  = $seq;
            $params = $this->get($param);
        }
        
        // 2. doesn't exists, search by Unique Key (non-caching)
        if (empty($params) === true && $id !== null) {
            $params = CUnique::instance()->get($id, false);
        }
        
        if ($param !== null && $params !== false) {
            if (empty($params) === false) {
                if (
                    ($seq === 0 || ($seq > 0 && $params['seq'] === $seq))
                 && $id !== null && $params['id'] === $id
                ) {
                    $param = $params[$this->index];
                    $params['date_login'] = $now;
                    $rtn = $this->set($param, $params);
                } else {
                    $rtn = false;
                    Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
                    Packet::set(FDEF_PACKET_RES_ERRTEXT, 'invalid request');
                    
                    Debug::log('useq: ' . $seq . ', uid: ' . $id . ', get: ' . json_encode($params));
                }
            } else if (empty($params) === true && $id !== null) {
                $params = $this->create($id);
                if (isset($params['id']) === false) {
                    $rtn = false;
                }
            }
        }
        
        if ($rtn === true) {
            Packet::set('user', $params);
        }
        
        return $rtn;
    }
    
    public function join()
    {
        $rtn = true;
        
        $uid = Packet::get('uid');
        $mnt = $this->create($uid);
        
        if (isset($mnt[$this->index]) === true) {
            Packet::set('user', $mnt);
        } else {
            $rtn = false;
        }
        
        return $rtn;
    }
    
    public function block()
    {
        return null;
    }
    
    public function unblock()
    {
        return null;
    }
    
    public function leave()
    {
        return null;
    }
    
    public function unleave()
    {
        return null;
    }
    
    public function reborn()
    {
        return null;
    }
    
    public function testMulti()
    {
        Packet::recv();
        
        $seqs = [1, 2];
        $ids  = ['module_user_cuser_id_1', 'module_user_cuser_id_2'];
        
        $gets = $this->gets($seqs);
        $vals = [];
        
        if (empty($gets) === true) {
            $gets = [[], []];
        }
        
        foreach ($gets as $k => $v) {
            if (isset($v[$this->index]) === true) {
                $v['date_login'] = Util::date();
                $vals[$k] = $v;
            } else {
                $vals[$k] = $this->creator($ids[$k]);
                $seqs[$k] = &$vals[$k][$this->index];
            }
        }
        
        $this->set($seqs, $vals);
        $gets = $this->gets($seqs);
        Packet::set('user-test', $gets);
        
        Packet::send();
    }
}