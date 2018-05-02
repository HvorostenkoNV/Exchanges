<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    InvalidArgumentException,
    Main\Data\Set;
/** ***********************************************************************************************
 * Parent class for testing Main\Data\Set classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class SetDataClass extends ExchangeTestCase
{
    protected static $setClassName = '';
    /** **********************************************************************
     * get correct data
     *
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        return [];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                   incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        return [];
    }
    /** **********************************************************************
     * get new queue object
     *
     * @return  Set                     new queue object
     ************************************************************************/
    final protected static function createSetObject() : Set
    {
        return new static::$setClassName;
    }
    /** **********************************************************************
     * check empty object
     *
     * @test
     ************************************************************************/
    public function emptyObject() : void
    {
        $set = self::createSetObject();

        self::assertTrue
        (
            $set->isEmpty(),
            'New '.static::$setClassName.' object is not empty'
        );
        self::assertEquals
        (
            0,
            $set->count(),
            'New '.static::$setClassName.' object values count is not zero'
        );
    }
    /** **********************************************************************
     * check read/write operations
     *
     * @test
     * @depends emptyObject
     ************************************************************************/
    public function readWriteOperations() : void
    {
        $set    = self::createSetObject();
        $values = static::getCorrectValues();

        if (count($values) <= 0)
        {
            self::assertTrue(true);
            return;
        }

        foreach ($values as $value)
        {
            $set->push($value);
        }

        self::assertFalse
        (
            $set->isEmpty(),
            'Filled '.static::$setClassName.' is empty'
        );
        self::assertEquals
        (
            count($values),
            $set->count(),
            'Filled '.static::$setClassName.' values count is not equal items count put'
        );

        for ($repeatLoop = 1; $repeatLoop <= 3; $repeatLoop++)
        {
            $startValuesIndex = 0;

            while ($set->valid())
            {
                self::assertEquals
                (
                    $values[$startValuesIndex],
                    $set->current(),
                    'Value put in '.static::$setClassName.' before not equals received'
                );
                self::assertEquals
                (
                    $startValuesIndex,
                    $set->key(),
                    'Value put in '.static::$setClassName.' before not equals received'
                );

                $set->next();
                $startValuesIndex++;
            }

            $set->rewind();
        }
    }
    /** **********************************************************************
     * check incorrect read/write operations
     *
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function incorrectReadWriteOperations() : void
    {
        $set                = self::createSetObject();
        $incorrectValues    = static::getIncorrectValues();

        self::assertNull
        (
            $set->current(),
            'Empty '.static::$setClassName.' must return null on call "current" method'
        );

        foreach ($incorrectValues as $value)
        {
            try
            {
                $set->push($value);
                self::fail('Expect '.InvalidArgumentException::class.' exception in '.static::$setClassName.' on push incorrect value '.var_export($value, true));
            }
            catch (InvalidArgumentException $error)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * check clearing operations
     *
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function clearingOperations() : void
    {
        $set    = self::createSetObject();
        $values = static::getCorrectValues();

        foreach ($values as $value)
        {
            $set->push($value);
        }

        $set->delete($values[array_rand($values)]);
        self::assertEquals
        (
            count($values) - 1,
            $set->count(),
            static::$setClassName.' values count not less by one after delete one item'
        );

        $set->clear();

        self::assertTrue
        (
            $set->isEmpty(),
            static::$setClassName.' is not empty after call "clear" method'
        );
        self::assertEquals
        (
            0,
            $set->count(),
            static::$setClassName.' values count is not zero after call "clear" method'
        );
    }
}