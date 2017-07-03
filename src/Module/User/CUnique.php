<?php
namespace Module\User;

use Module\User\CUser;

class CUnique extends CUser
{
    protected $index = 'id';
    protected $field = 'seq,cash,cash_free,date_login,date_logout';
    protected $types = [
        FDEF_TYPE_STRING,  // id
        FDEF_TYPE_INTEGER, // seq
        FDEF_TYPE_INTEGER, // cash
        FDEF_TYPE_INTEGER, // cash_free
        FDEF_TYPE_DATE,    // date_login
        FDEF_TYPE_DATE     // date_logout
    ];
}