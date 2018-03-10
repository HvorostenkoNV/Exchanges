<?php
declare(strict_types=1);

namespace Main\Data;
/** ***********************************************************************************************
 * Set of unique objects
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class SetData implements Set
{
	/** **********************************************************************
	 * clear data
	 ************************************************************************/
	public function clear() : void
	{

	}
	/** **********************************************************************
	 * get data count
	 * @return  int                     items count
	 ************************************************************************/
	public function count() : int
	{
		return 0;
	}
	/** **********************************************************************
	 * Return the current element
	 * @return  object                  current set object
	 ************************************************************************/
	public function current() : object
	{
		return new SetData;
	}
	/** **********************************************************************
	 * delete object from set
	 * @param   object  $object         object to delete
	 ************************************************************************/
	public function delete(object $object) : void
	{

	}
	/** **********************************************************************
	 * check data is empty
	 * @return  bool                    collection is empty
	 ************************************************************************/
	public function isEmpty() : bool
	{
		return true;
	}
	/** **********************************************************************
	 * Move forward to next element
	 ************************************************************************/
	public function next() : void
	{

	}
	/** **********************************************************************
	 * Return the key of the current element
	 * @return  int                     key of the current element
	 ************************************************************************/
	public function key() : int
	{
		return 0;
	}
	/** **********************************************************************
	 * push object to set
	 * @param   object  $object         object to add
	 ************************************************************************/
	public function push(object $object) :void
	{

	}
	/** **********************************************************************
	 * Rewind the Iterator to the first element
	 ************************************************************************/
	public function rewind() : void
	{

	}
	/** **********************************************************************
	 * Checks if current position is valid
	 * @return  bool                   valid or not
	 ************************************************************************/
	public function valid() : bool
	{
		return false;
	}
}