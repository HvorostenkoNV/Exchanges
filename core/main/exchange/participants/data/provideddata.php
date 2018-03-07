<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
	RuntimeException,
	InvalidArgumentException,
	Main\Data\QueueData,
	Main\Data\MapData;
/** ***********************************************************************************************
 * Participants provided data
 * @package exchange_exchange
 * @author  Hvorostenko
 * @example
 *  [
 *      MapData,
 *      MapData,
 *      MapData
 *  ]
 *************************************************************************************************/
class ProvidedData extends QueueData implements Data
{
	/** **********************************************************************
	 * get data form queue start
	 * @return  MapData             data
	 * @throws  RuntimeException    if no data for pop
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