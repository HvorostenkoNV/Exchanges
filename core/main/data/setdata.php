<?php
declare(strict_types=1);

namespace Main\Data;

use SplObjectStorage;
/** ***********************************************************************************************
 * Set data, set of unique objects
 *
 * @package exchange_data
 * @author  Hvorostenko
 *************************************************************************************************/
class SetData implements Set
{
    private $set = null;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        $this->set = new SplObjectStorage;
    }
    /** **********************************************************************
     * clear set
     *
     * @return void
     ************************************************************************/
    public function clear() : void
    {
        $this->set = new SplObjectStorage;
    }
    /** **********************************************************************
     * get set count
     *
     * @return  int                         set count
     ************************************************************************/
    public function count() : int
    {
        return $this->set->count();
    }
    /** **********************************************************************
     * get current item
     *
     * @return  mixed|null                  current item or null
     ************************************************************************/
    public function current()
    {
        return $this->set->current();
    }
    /** **********************************************************************
     * drop item from set
     *
     * @param   object  $object             item for drop
     * @return  void
     ************************************************************************/
    public function delete($object) : void
    {
        $this->set->detach($object);
    }
    /** **********************************************************************
     * check set is empty
     *
     * @return  bool                        set is empty
     ************************************************************************/
    public function isEmpty() : bool
    {
        return $this->set->count() <= 0;
    }
    /** **********************************************************************
     * move forward to next item
     *
     * @return void
     ************************************************************************/
    public function next() : void
    {
        $this->set->next();
    }
    /** **********************************************************************
     * get current item key
     *
     * @return  int                         current item key
     ************************************************************************/
    public function key() : int
    {
        return $this->set->key();
    }
    /** **********************************************************************
     * push item to set
     *
     * @param   object  $object             pushed item
     * @return  void
     ************************************************************************/
    public function push($object) :void
    {
        $this->set->attach($object);
    }
    /** **********************************************************************
     * rewind iterator to the first item
     *
     * @return void
     ************************************************************************/
    public function rewind() : void
    {
        $this->set->rewind();
    }
    /** **********************************************************************
     * checks current position is valid
     *
     * @return  bool                        current position is valid
     ************************************************************************/
    public function valid() : bool
    {
        return $this->set->valid();
    }
}