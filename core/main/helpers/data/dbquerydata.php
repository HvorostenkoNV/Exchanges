<?php
declare(strict_types=1);

namespace Main\Helpers\Data;

use
	SplQueue,
	RuntimeException,
	Main\Data\MapData;
/** ***********************************************************************************************
 * DB query data, type of "First In, First Out"
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class DBQueryData implements DBQuery
{
	private $splQueue = NULL;
	/** **********************************************************************
	 * construct
	 ************************************************************************/
	public function __construct()
	{
		$this->splQueue = new SplQueue;
	}
	/** **********************************************************************
	 * clear data
	 ************************************************************************/
	public function clear() : void
	{
		$this->splQueue = new SplQueue;
	}
	/** **********************************************************************
	 * get data count
	 ************************************************************************/
	public function count() : int
	{
		return $this->splQueue->count();
	}
	/** **********************************************************************
	 * check data is empty
	 * @return  bool                collection is empty
	 ************************************************************************/
	public function isEmpty() : bool
	{
		return $this->splQueue->isEmpty();
	}
	/** **********************************************************************
	 * get data form queue start
	 * @return  MapData             data
	 * @throws  RuntimeException    if no data for pop
	 ************************************************************************/
	public function pop() : MapData
	{
		try
		{
			return $this->splQueue->dequeue();
		}
		catch( RuntimeException $error )
		{
			throw $error;
		}
	}
	/** **********************************************************************
	 * get data form queue start
	 * @param   MapData $data       data
	 ************************************************************************/
	public function push(MapData $data) : void
	{
		$this->splQueue->enqueue($data);
	}
}