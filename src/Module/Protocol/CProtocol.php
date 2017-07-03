<?php
namespace Module\Protocol;

use Common\Base;
use Common\Debug;
use Common\Packet;

class CProtocol extends Base
{
    const SERVER_VERSION     = 1000;
    const SERVER_CLIENTCRASH = 1001;
    const USER_LOGIN         = 2000;
    const CHARACTER_LIST     = 3000;
    const CHARACTER_ADD      = 3001;

    public function run()
    {
        $rtn = null;
        
        Packet::recv();
        
        $protocol = (int)Packet::get('cmd');
        
        if ($protocol !== null) {
            switch ($protocol) {
                case self::SERVER_VERSION:
                    $rtn = \Module\Version\CVersion::instance()->version();
                    break;
                case self::SERVER_CLIENTCRASH:
                    $rtn = \Module\Log\CLog::instance()->crash();
                    break;
                case self::USER_LOGIN:
                    $rtn = \Module\User\CUser::instance()->login();
                    break;
                case self::CHARACTER_LIST:
                    $rtn = \Module\Character\CCharacter::instance()->lists();
                    break;
                case self::CHARACTER_ADD:
                    $rtn = \Module\Character\CCharacter::instance()->add();
                    break;
                default:
                    Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
                    Packet::set(FDEF_PACKET_RES_ERRTEXT, 'doesn\'t exists protocol');
            }
        }
        
        if ($rtn === false) {
            Packet::set(FDEF_PACKET_RES_ERRCODE, FDEF_ERROR_APPL);
            Packet::set(FDEF_PACKET_RES_ERRTEXT, 'an error has occurred');
        }
        
        Packet::send();
        
        return $rtn;
    }
}