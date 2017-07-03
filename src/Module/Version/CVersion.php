<?php
namespace Module\Version;

use Common\Data;
use Common\Debug;
use Common\Packet;
use Common\Util;

class CVersion extends Data
{
    protected $label = 'user';
    
    protected $table = 'tb_version';
    protected $incrx;
    protected $index = 'major,minor,micro,shop';
    protected $field = 'status,stable';
    protected $types = [
        FDEF_TYPE_INTEGER, // major
        FDEF_TYPE_INTEGER, // minor
        FDEF_TYPE_INTEGER, // micro
        FDEF_TYPE_STRING,  // shop
        FDEF_TYPE_INTEGER, // status
        FDEF_TYPE_INTEGER  // stable
    ];
    
    protected function creator($k)
    {
        $rtn = parent::creator($k);
        
        $now = Util::date();
        
        $rtn['date_create'] = $now;
        
        return $rtn;
    }
    
    public function version()
    {
        $rtn = true;
        
        $major = (int)Packet::get('major');
        $minor = (int)Packet::get('minor');
        $micro = (int)Packet::get('micro');
        $shop  = Packet::get('shop');
        
        Debug::log('major: ' . $major . ', minor: ' . $minor . ', micro: ' . $micro . ', shop: ' . $shop);
        
        if ($major > 0 && $minor > 0 && $micro > 0 && empty($shop) === false) {
            $k = $this->valueImplodeByIndex([
                'major' => $major,
                'minor' => $minor,
                'micro' => $micro,
                'shop'  => $shop
            ]);
            
            $params = $this->get($k);
            Packet::set('version', $params);
        } else {
            $rtn = false;
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'invalid request');
        }
        
        return $rtn;
    }
}