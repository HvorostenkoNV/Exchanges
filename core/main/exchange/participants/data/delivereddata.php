<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
	InvalidArgumentException,
	Main\Data\QueueData;
/** ***********************************************************************************************
 * Participants delivered data. Data ready for delivery. Data type of "First In, First Out". Collection of DBFieldsValues objects
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class DeliveredData extends QueueData implements Data
{
	/** **********************************************************************
	 * get data form queue start
	 * @return  ItemData                    data
	 ************************************************************************/
	public function pop()
	{
		return parent::pop();
	}
	/** **********************************************************************
	 * get data form queue start
	 * @param   ItemData    $data           data
	 * @throws  InvalidArgumentException    expect ItemData data
	 ************************************************************************/
	public function push($data) : void
	{
		if( !$data instanceof ItemData )
			throw new InvalidArgumentException('Pushed data required to be '.ItemData::class.' object');

		parent::push($data);
	}
}