<?php
declare(strict_types=1);

namespace Main\Data;

use Countable;
/** ***********************************************************************************************
 * Data interface, base data interface
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
interface Data extends Countable
{
	/** **********************************************************************
	 * clear data
	 ************************************************************************/
	public function clear() : void;
	/** **********************************************************************
	 * get data count
	 * @return  int                     items count
	 ************************************************************************/
	public function count() : int;
	/** **********************************************************************
	 * check data is empty
	 * @return  bool                    collection is empty
	 ************************************************************************/
	public function isEmpty() : bool;
}