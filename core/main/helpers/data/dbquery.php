<?php
declare(strict_types=1);

namespace Main\Helpers\Data;

use
	Countable,
	RuntimeException,
	Main\Data\Data,
	Main\Data\MapData;
/** ***********************************************************************************************
 * DB query, data type of "First In, First Out"
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
interface DBQuery extends Data, Countable
{
	/** **********************************************************************
	 * get data form queue start
	 * @return  MapData             data
	 * @throws  RuntimeException    if no data for pop
	 ************************************************************************/
	public function pop() : MapData;
	/** **********************************************************************
	 * get data form queue start
	 * @param   MapData $data       data
	 ************************************************************************/
	public function push(MapData $data) : void;
}