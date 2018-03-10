<?php
declare(strict_types=1);

namespace Main\Data;

use InvalidArgumentException;
/** ***********************************************************************************************
 * Map data, collection of key => values
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class MapData implements Map
{
	private $data = [];
	/** **********************************************************************
	 * construct
	 * @param   array   $data               data
	 ************************************************************************/
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}
	/** **********************************************************************
	 * delete value by index
	 * @param   mixed   $key                value index
	 ************************************************************************/
	public function delete($key) : void
	{
		if( array_key_exists($key, $this->data) )
			unset($this->data[$key]);
	}
	/** **********************************************************************
	 * clear data
	 ************************************************************************/
	public function clear() : void
	{
		$this->data = [];
	}
	/** **********************************************************************
	 * get data count
	 ************************************************************************/
	public function count() : int
	{
		return count($this->data);
	}
	/** **********************************************************************
	 * get value by index
	 * @param   mixed   $key                value index
	 * @return  mixed                       value
	 ************************************************************************/
	public function get($key)
	{
		return array_key_exists($key, $this->data)
			? $this->data[$key]
			: NULL;
	}
	/** **********************************************************************
	 * get value by index
	 * @return  array                       keys queue
	 ************************************************************************/
	public function getKeys() : array
	{
		return array_keys($this->data);
	}
	/** **********************************************************************
	 * check data is empty
	 * @return  bool                        collection is empty
	 ************************************************************************/
	public function isEmpty() : bool
	{
		return count($this->data) <= 0;
	}
	/** **********************************************************************
	 * attach value to index
	 * @param   mixed   $key                value index
	 * @param   mixed   $value              value
	 * @throws  InvalidArgumentException    incorrect key type
	 ************************************************************************/
	public function set($key, $value) : void
	{
		if( !is_string($key) && !is_int($key) )
			throw new InvalidArgumentException('Incorrect key type');

		$this->data[$key] = $value;
	}
}