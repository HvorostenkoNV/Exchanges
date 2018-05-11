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
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    abstract public static function getSetClassName() : string;
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    abstract public static function getCorrectDataValues() : array;
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    abstract public static function getIncorrectDataValues() : array;
    /** **********************************************************************
     * check empty object
     *
     * @test
     * @throws
     ************************************************************************/
    public function emptyObject() : void
    {
        $set        = $this->createSetObject();
        $className  = static::getSetClassName();

        self::assertTrue
        (
            $set->isEmpty(),
            "New $className object is not empty"
        );
        self::assertEquals
        (
            0,
            $set->count(),
            "New $className object values count is not zero"
        );
    }
    /** **********************************************************************
     * check read/write operations
     *
     * @test
     * @depends emptyObject
     * @throws
     ************************************************************************/
    public function readWriteOperations() : void
    {
        $set        = $this->createSetObject();
        $values     = static::getCorrectDataValues();
        $className  = static::getSetClassName();

        if (count($values) <= 0)
        {
            self::markTestSkipped("No \"correct\" values for testing $className class");
            return;
        }

        foreach ($values as $value)
        {
            $set->push($value);
        }

        self::assertFalse
        (
            $set->isEmpty(),
            "Filled $className is empty"
        );
        self::assertEquals
        (
            count($values),
            $set->count(),
            "Filled $className values count is not equal items count put"
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
                    "Value put in $className before not equals received"
                );
                self::assertEquals
                (
                    $startValuesIndex,
                    $set->key(),
                    "Value put in $className before not equals received"
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
     * @throws
     ************************************************************************/
    public function incorrectReadWriteOperations() : void
    {
        $set                = $this->createSetObject();
        $incorrectValues    = static::getIncorrectDataValues();
        $className          = static::getSetClassName();

        if (count($incorrectValues) <= 0)
        {
            self::markTestSkipped("No \"incorrect\" values for testing $className class");
            return;
        }

        self::assertNull
        (
            $set->current(),
            "Empty $className must return null on call \"current\" method"
        );

        foreach ($incorrectValues as $value)
        {
            try
            {
                $exceptionName  = InvalidArgumentException::class;
                $valuePrintable = var_export($value, true);

                $set->push($value);
                self::fail("Expect $exceptionName exception in $className on push incorrect value $valuePrintable");
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
     * @throws
     ************************************************************************/
    public function clearingOperations() : void
    {
        $set        = $this->createSetObject();
        $values     = static::getCorrectDataValues();
        $className  = static::getSetClassName();

        foreach ($values as $value)
        {
            $set->push($value);
        }

        $set->delete($values[array_rand($values)]);
        self::assertEquals
        (
            count($values) - 1,
            $set->count(),
            "$className values count not less by one after delete one item"
        );

        $set->clear();

        self::assertTrue
        (
            $set->isEmpty(),
            "$className is not empty after call \"clear\" method"
        );
        self::assertEquals
        (
            0,
            $set->count(),
            "$className values count is not zero after call \"clear\" method"
        );
    }
    /** **********************************************************************
     * get new queue object
     *
     * @return  Set                     new queue object
     ************************************************************************/
    private function createSetObject() : Set
    {
        $className = static::getSetClassName();

        return new $className;
    }
}