<?php
declare(strict_types=1);

namespace Main\Data;
/** ***********************************************************************************************
 * Map data interface, collection of key => values
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
interface Map extends Data
{
	/** **********************************************************************
	 * construct
	 * @param   array   $data   data
	 ************************************************************************/
	public function __construct(array $data = []);
	/** **********************************************************************
	 * delete value by index
	 * @param   mixed   $key    value index
	 ************************************************************************/
	public function delete($key) : void;
	/** **********************************************************************
	 * get value by index
	 * @param   mixed   $key    value index
	 * @return  mixed           value
	 ************************************************************************/
	public function get($key);
	/** **********************************************************************
	 * get value by index
	 * @return  string[]        keys queue
	 ************************************************************************/
	public function getKeys() : array;
	/** **********************************************************************
	 * attach value to index
	 * @param   mixed   $key    value index
	 * @param   mixed   $value  value
	 ************************************************************************/
	public function set($key, $value) : void;
}