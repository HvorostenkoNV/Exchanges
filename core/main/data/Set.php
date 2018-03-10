<?php
declare(strict_types=1);

namespace Main\Data;

use Iterator;
/** ***********************************************************************************************
 * Set interface, set of unique objects
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
interface Set extends Iterator
{
	/** **********************************************************************
	 * Return the current element
	 * @return  object                  current set object
	 ************************************************************************/
	public function current() : object;
	/** **********************************************************************
	 * delete object from set
	 * @param   object  $object         object to delete
	 ************************************************************************/
	public function delete(object $object) : void;
	/** **********************************************************************
	 * Move forward to next element
	 ************************************************************************/
	public function next() : void;
	/** **********************************************************************
	 * Return the key of the current element
	 * @return  int                     key of the current element
	 ************************************************************************/
	public function key() : int;
	/** **********************************************************************
	 * push object to set
	 * @param   object  $object         object to add
	 ************************************************************************/
	public function push(object $object) :void;
	/** **********************************************************************
	 * Rewind the Iterator to the first element
	 ************************************************************************/
	public function rewind() : void;
	/** **********************************************************************
	 * Checks if current position is valid
	 * @return  bool                   valid or not
	 ************************************************************************/
	public function valid() : bool;
}