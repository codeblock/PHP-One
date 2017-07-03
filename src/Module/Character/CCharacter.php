<?php
namespace Module\Character;

use Common\Data;
use Common\Debug;
use Common\Packet;
use Common\Util;
use Module\Character\CUnique;
use Module\Character\CForeign;

class CCharacter extends Data
{
    protected $label = 'user';
    
    protected $table = 'tb_character';
    protected $incrx = 'seq';
    protected $index = 'seq';
    protected $field = 'name,gold,gold_free';
    protected $types = [
        FDEF_TYPE_INTEGER, // seq
        FDEF_TYPE_STRING,  // name
        FDEF_TYPE_INTEGER, // gold
        FDEF_TYPE_INTEGER, // gold_free
    ];
    
    protected function creator($k)
    {
        $rtn = parent::creator($k);
        
        $now = Util::date();
        
        $rtn['name']        = $k;
        $rtn['gold']        = 0;
        $rtn['gold_free']   = 2000;
        $rtn['date_create'] = $now;
        
        return $rtn;
    }
    
    public function key($k)
    {
        $k = $this->table . $this->key_glue . $this->index . $this->key_glue . $k;
        
        return $this->key_prefix . $k . $this->key_suffix;
    }
    
    private function create($k, $user_seq)
    {
        $rtn = null;
        
        $params = $this->creator($k);
        $params['user_seq'] = $user_seq;
        
        $mnt = $this->set($params[$this->index], $params);
        if ($mnt === true) {
            $rtn = $this->get($params[$this->index]);
        }
        
        return $rtn;
    }
    
    /**
     * unique name check
     * 
     * @param string $name
     * @return boolean
     */
    public function exist($name)
    {
        $rtn = false;
        
        $exist = CUnique::instance()->get($name, false);
        if (isset($exist['name']) === true) {
            $rtn = true; // can't create
        }
        
        return $rtn;
    }
    
    /**
     * character list of users
     * 
     * @return boolean
     */
    public function lists()
    {
        $rtn = true;
        
        $user_seq = (int)Packet::get('useq');
        
        if ($user_seq > 0) {
            // ------------------------------------- pre-work
            $lists = CForeign::instance()->get($user_seq);
            // ------------------------------------- pre-work
            
            // ------------------------------------- real-work
            $chars = [];
            if (empty($lists['seqs']) === false) {
                $seqs  = explode(',', $lists['seqs']);
                $chars = $this->gets($seqs);
            }
            // ------------------------------------- real-work
            
            if ($chars !== false) {
                // Linq::select ...
                
                Packet::set('character', $chars);
            }
        } else {
            $rtn = false;
        }
        
        return $rtn;
    }
    
    public function add()
    {
        $rtn = true;
        
        $name     = Packet::get('name');
        $user_seq = (int)Packet::get('useq');
        
        if (empty($name) === true || $user_seq <= 0) {
            $rtn = false;
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'invalid request');
        } else {
            if ($this->exist($name) === true) {
                $rtn = false;
                Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
                Packet::set(FDEF_PACKET_RES_ERRTEXT, 'name is already exists');
            }
        }
        
        if ($rtn === true) {
            $mnt = $this->create($name, $user_seq);
            if (isset($mnt[$this->index]) === false) {
                $rtn = false;
                Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
                Packet::set(FDEF_PACKET_RES_ERRTEXT, 'creation failure');
            } else {
                CForeign::instance()->reload($user_seq);
                
                if ($rtn === true) {
                    Packet::set('character', $mnt);
                }
            }
        }
        
        return $rtn;
    }
}