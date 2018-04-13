<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Main\Exchange\Participants\Users1C,
    Main\Exchange\Participants\UsersAD,
    Main\Exchange\Participants\UsersBitrix;
/** ***********************************************************************************************
 * Users exchange procedure
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class UsersExchange extends AbstractProcedure
{
    protected $participantsClasses =
    [
        Users1C::class,
        UsersAD::class,
        UsersBitrix::class
    ];
}