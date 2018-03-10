<?php
declare(strict_types=1);

namespace Main\Helpers\Data;

use
	InvalidArgumentException,
	Main\Data\QueueData;
/** ***********************************************************************************************
 * DB query data. Data type of "First In, First Out". Collection of DBFieldsValues objects
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class DBQueryResult extends QueueData
{
	/** **********************************************************************
	 * get data form queue start
	 * @return  DBFieldsValues              data
	 ************************************************************************/
	public function pop()
	{
		return parent::pop();
	}
	/** **********************************************************************
	 * get data form queue start
	 * @param   DBFieldsValues  $data       data
	 * @throws  InvalidArgumentException    pushed data is not MapData
	 ************************************************************************/
	public function push($data) : void
	{
		if( !$data instanceof DBFieldsValues )
			throw new InvalidArgumentException('Pushed data required to be '.DBFieldsValues::class.' object');

		parent::push($data);
	}
}