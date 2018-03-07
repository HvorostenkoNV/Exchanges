<?php
declare(strict_types=1);

namespace Main\Helpers;

use
	RuntimeException,
	InvalidArgumentException,
	Main\Data\MapData,
	Main\Data\QueueData;
/** ***********************************************************************************************
 * DB query data
 * @package exchange_helpers
 * @author  Hvorostenko
 * @example
 *  [
 *      MapData,
 *      MapData,
 *      MapData
 *  ]
 *************************************************************************************************/
class DBQueryData extends QueueData
{
	/** **********************************************************************
	 * get data form queue start
	 * @return  MapData                     data
	 * @throws  RuntimeException            if no data for pop
	 ************************************************************************/
	public function pop()
	{
		try
		{
			return parent::pop();
		}
		catch( RuntimeException $error )
		{
			throw $error;
		}
	}
	/** **********************************************************************
	 * get data form queue start
	 * @param   MapData $data               data
	 * @throws  InvalidArgumentException    expect MapData data
	 ************************************************************************/
	public function push($data) : void
	{
		if( !$data instanceof MapData )
		{
			throw new InvalidArgumentException('Pushed data have to be instance of '.MapData::class);
		}
		parent::push($data);
	}
}