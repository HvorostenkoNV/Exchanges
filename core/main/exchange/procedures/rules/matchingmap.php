<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use
	InvalidArgumentException,
	Main\Data\MapData;
/** ***********************************************************************************************
 * Procedures fields matching map
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class MatchingMap extends MapData
{
	/** **********************************************************************
	 * construct
	 * @param   array   $data               data
	 * @throws  InvalidArgumentException    incorrect array
	 ************************************************************************/
	public function __construct(array $data = [])
	{
		foreach( $data as $key => $value )
		{
			if( !(is_string($key) && class_exists($key)) )
				throw new InvalidArgumentException('Incorrect array data. Data keys must class names.');
			if( !is_string($value) )
				throw new InvalidArgumentException('Incorrect array data. Data values must be string. '.gettype($value).' cached');
		}

		parent::__construct($data);
	}
	/** **********************************************************************
	 * delete value by index
	 * @param   string  $key                value index
	 ************************************************************************/
	public function delete($key) : void
	{
		parent::delete($key);
	}
	/** **********************************************************************
	 * get value by index
	 * @param   string  $key                value index
	 * @return  string                      value
	 ************************************************************************/
	public function get($key)
	{
		return parent::get($key);
	}
	/** **********************************************************************
	 * get value by index
	 * @return  string[]                    keys queue
	 ************************************************************************/
	public function getKeys() : array
	{
		return parent::getKeys();
	}
	/** **********************************************************************
	 * attach value to index
	 * @param   string  $key                value index
	 * @param   mixed   $value              value
	 * @throws  InvalidArgumentException    incorrect key type
	 ************************************************************************/
	public function set($key, $value) : void
	{
		if( !(is_string($key) && class_exists($key)) )
			throw new InvalidArgumentException('Key must be class name');
		if( !is_string($value) )
			throw new InvalidArgumentException('Value must be string. '.gettype($value).' cached');

		parent::set($key, $value);
	}
}