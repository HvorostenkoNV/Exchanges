<?php
declare(strict_types=1);

namespace Main\Data;

use Iterator;
/** ***********************************************************************************************
 * Set interface, collection of unique objects
 *
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
interface Set extends Data, Iterator
{
    /** **********************************************************************
     * get current item
     *
     * @return  object|null                 current item or null
     ************************************************************************/
    public function current() : ?object;
    /** **********************************************************************
     * drop item from set
     *
     * @param   object  $object             item for drop
     ************************************************************************/
    public function delete(object $object) : void;
    /** **********************************************************************
     * move forward to next item
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
     ************************************************************************/
    public function push(object $object) :void;
    /** **********************************************************************
     * rewind iterator to the first item
     ************************************************************************/
    public function rewind() : void;
    /** **********************************************************************
     * checks current position is valid
     *
     * @return  bool                        current position is valid
     ************************************************************************/
    public function valid() : bool;
}