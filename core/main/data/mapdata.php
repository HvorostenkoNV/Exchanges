<?php
declare(strict_types=1);

namespace Main\Data;

use Throwable;
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
	 * @param   array   $data   data
	 ************************************************************************/
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}
	/** **********************************************************************
	 * delete value by index
	 * @param   mixed   $key    value index
	 ************************************************************************/
	public function delete($key) : void
	{
		$dataKey = '';
		try                         {$dataKey = strval($key);}
		catch( Throwable $error )   {}

		if( strlen($dataKey) > 0 && array_key_exists($dataKey, $this->data) )
			unset($this->data[$dataKey]);
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
	 * @param   mixed   $key    value index
	 * @return  mixed           value
	 ************************************************************************/
	public function get($key)
	{
		$dataKey = '';
		try                         {$dataKey = strval($key);}
		catch( Throwable $error )   {}

		return strlen($dataKey) > 0 && array_key_exists($dataKey, $this->data)
			? $this->data[$dataKey]
			: NULL;
	}
	/** **********************************************************************
	 * get value by index
	 * @return  string[]        keys queue
	 ************************************************************************/
	public function getKeys() : array
	{
		return array_keys($this->data);
	}
	/** **********************************************************************
	 * check data is empty
	 * @return  bool            collection is empty
	 ************************************************************************/
	public function isEmpty() : bool
	{
		return count($this->data) <= 0;
	}
	/** **********************************************************************
	 * attach value to index
	 * @param   mixed   $key    value index
	 * @param   mixed   $value  value
	 ************************************************************************/
	public function set($key, $value) : void
	{
		$dataKey = '';
		try                         {$dataKey = strval($key);}
		catch( Throwable $error )   {}

		$this->data[$dataKey] = $value;
	}
}