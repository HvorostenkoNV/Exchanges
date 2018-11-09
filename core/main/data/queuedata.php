<?php
declare(strict_types=1);

namespace Main\Data;

use
	SplQueue,
	RuntimeException;
/** ***********************************************************************************************
 * Queue data, collection type of "First In, First Out"
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
class QueueData implements Queue
{
	private $splQueue = null;
	/** **********************************************************************
	 * construct
	 ************************************************************************/
	public function __construct()
	{
		$this->splQueue = new SplQueue;
	}
	/** **********************************************************************
	 * clear queue
     *
     * @return void
	 ************************************************************************/
	public function clear() : void
	{
		$this->splQueue = new SplQueue;
	}
	/** **********************************************************************
	 * get queue count
     *
     * @return  int                         queue count
	 ************************************************************************/
	public function count() : int
	{
		return $this->splQueue->count();
	}
	/** **********************************************************************
	 * check queue is empty
     *
	 * @return  bool                        queue is empty
	 ************************************************************************/
	public function isEmpty() : bool
	{
		return $this->splQueue->isEmpty();
	}
	/** **********************************************************************
     * extract queue data from the start
     *
	 * @return  mixed                       data
	 * @throws  RuntimeException            if no data for extract
	 ************************************************************************/
	public function pop()
	{
		try
		{
			return $this->splQueue->dequeue();
		}
		catch (RuntimeException $error)
		{
			throw $error;
		}
	}
	/** **********************************************************************
     * push data to the end
     *
	 * @param   mixed   $data               data
     * @return  void
	 ************************************************************************/
	public function push($data) : void
	{
		$this->splQueue->enqueue($data);
	}
}