<?php
namespace Module\Character;

use Module\Character\CCharacter;

class CForeign extends CCharacter
{
    protected $index = 'user_seq';
    protected $field = 'ROW_CONCAT(seq) AS seqs';
    protected $types = [
        FDEF_TYPE_INTEGER,  // user_seq
        FDEF_TYPE_STRING,   // seqs
    ];
    
    /**
     * prevent direct set : include field with function
     * 
     * {@inheritDoc}
     * @see \Common\Data::set()
     */
    protected function set($keys, $vals, $caching = false)
    {
        return false;
    }
}