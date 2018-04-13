<?php
declare(strict_types=1);

namespace Main\Data;

use SplObjectStorage;
/** ***********************************************************************************************
 * Set of unique objects
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
class SetData implements Set
{
    private $set = NULL;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        $this->set = new SplObjectStorage;
    }
    /** **********************************************************************
     * clear data
     ************************************************************************/
    public function clear() : void
    {
        $this->set = new SplObjectStorage;
    }
    /** **********************************************************************
     * get data count
     * @return  int                     items count
     ************************************************************************/
    public function count() : int
    {
        return $this->set->count();
    }
    /** **********************************************************************
     * Return the current element
     * @return  object|NULL             current set object or NULL
     ************************************************************************/
    public function current() : ?object
    {
        return $this->set->current();
    }
    /** **********************************************************************
     * delete object from set
     * @param   object  $object         object to delete
     ************************************************************************/
    public function delete(object $object) : void
    {
        $this->set->detach($object);
    }
    /** **********************************************************************
     * check data is empty
     * @return  bool                    collection is empty
     ************************************************************************/
    public function isEmpty() : bool
    {
        return $this->set->count() <= 0;
    }
    /** **********************************************************************
     * Move forward to next element
     ************************************************************************/
    public function next() : void
    {
        $this->set->next();
    }
    /** **********************************************************************
     * Return the key of the current element
     * @return  int                     key of the current element
     ************************************************************************/
    public function key() : int
    {
        return $this->set->key();
    }
    /** **********************************************************************
     * push object to set
     * @param   object  $object         object to add
     ************************************************************************/
    public function push(object $object) :void
    {
        $this->set->attach($object);
    }
    /** **********************************************************************
     * Rewind the Iterator to the first element
     ************************************************************************/
    public function rewind() : void
    {
        $this->set->rewind();
    }
    /** **********************************************************************
     * Checks if current position is valid
     * @return  bool                    valid or not
     ************************************************************************/
    public function valid() : bool
    {
        return $this->set->valid();
    }
}