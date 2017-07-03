<?php
namespace Module\Character;

use Module\Character\CCharacter;

class CUnique extends CCharacter
{
    protected $index = 'name';
    protected $field = 'seq,gold,gold_free';
    protected $types = [
        FDEF_TYPE_STRING,  // name
        FDEF_TYPE_INTEGER, // seq
        FDEF_TYPE_INTEGER, // gold
        FDEF_TYPE_INTEGER, // gold_free
    ];
}