<?php
declare(strict_types=1);

namespace Main\Data;

use
	SplQueue,
	RuntimeException;
/** ***********************************************************************************************
 * Queue, data type of "First In, First Out"
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class QueueData implements Queue
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
	 * @return  mixed               data
	 * @throws  RuntimeException    if no data for pop
	 ************************************************************************/
	public function pop()
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
	 * @param   mixed   $data       data
	 ************************************************************************/
	public function push($data) : void
	{
		$this->splQueue->enqueue($data);
	}
}