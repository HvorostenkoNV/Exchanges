<?php
declare(strict_types=1);

namespace Main\Data;

use Iterator;
/** ***********************************************************************************************
 * Set interface, collection of unique objects
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
interface Set extends Data, Iterator
{
    /** **********************************************************************
     * get current item
     *
     * @return  mixed|null                  current item or null
     ************************************************************************/
    public function current();
    /** **********************************************************************
     * drop item from set
     *
     * @param   object  $object             item for drop
     * @return  void
     ************************************************************************/
    public function delete($object) : void;
    /** **********************************************************************
     * move forward to next item
     *
     * @return void
     ************************************************************************/
    public function next() : void;
    /** **********************************************************************
     * get current item key
     *
     * @return  int                         current item key
     ************************************************************************/
    public function key() : int;
    /** **********************************************************************
     * push item to set
     *
     * @param   object  $object             pushed item
     * @return  void
     ************************************************************************/
    public function push($object) :void;
    /** **********************************************************************
     * rewind iterator to the first item
     *
     * @return void
     ************************************************************************/
    public function rewind() : void;
    /** **********************************************************************
     * checks current position is valid
     *
     * @return  bool                        current position is valid
     ************************************************************************/
    public function valid() : bool;
}