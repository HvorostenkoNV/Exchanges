<?php
namespace Main\Exchange\Procedures;

use Main\Exchange\Participants\
	{
		Users1C,
		UsersAD,
		UsersBitrix
	};

class UsersExchange extends AbstractProcedure
{
	protected $participantsClasses =
	[
		Users1C::class,
		UsersAD::class,
		UsersBitrix::class
	];
}