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
     * check read/write value operations
     *
     * @test
     * @throws
     ************************************************************************/
    public function readWriteValue() : void
    {
        $set            = $this->createSetObject();
        $className      = static::getSetClassName();
        $correctValues  = static::getCorrectDataValues();

        if (count($correctValues) <= 0)
        {
            self::markTestSkipped("No \"correct\" values for testing \"$className\" class");
            return;
        }

        foreach ($correctValues as $value)
        {
            $set->push($value);
        }

        for ($repeatLoop = 1; $repeatLoop <= 5; $repeatLoop++)
        {
            $startValuesIndex = 0;

            while ($set->valid())
            {
                self::assertEquals
                (
                    $correctValues[$startValuesIndex],
                    $set->current(),
                    "Expect get value equals seted before in \"$className\""
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
     * check counting operations
     *
     * @test
     * @depends readWriteValue
     * @throws
     ************************************************************************/
    public function counting() : void
    {
        $set            = $this->createSetObject();
        $className      = static::getSetClassName();
        $correctValues  = static::getCorrectDataValues();

        self::assertTrue
        (
            $set->isEmpty(),
            "New \"$className\" object is not empty"
        );
        self::assertEquals
        (
            0,
            $set->count(),
            "New \"$className\" object values count is not zero"
        );

        foreach ($correctValues as $values)
        {
            $set->push($values);
        }

        self::assertFalse
        (
            $set->isEmpty(),
            "Filled \"$className\" is empty"
        );
        self::assertEquals
        (
            count($correctValues),
            $set->count(),
            "Filled \"$className\" values count is not equal items count put"
        );
    }
    /** **********************************************************************
     * check read/write value operations with incorrect values
     *
     * @test
     * @depends readWriteValue
     * @throws
     ************************************************************************/
    public function usingIncorrectValues() : void
    {
        $set                = $this->createSetObject();
        $className          = static::getSetClassName();
        $incorrectValues    = static::getIncorrectDataValues();
        $exceptionName      = InvalidArgumentException::class;

        if (count($incorrectValues) <= 0)
        {
            self::markTestSkipped("No \"incorrect\" data values for testing \"$className\" class");
            return;
        }

        self::assertNull
        (
            $set->current(),
            "Empty \"$className\" must return null on call \"current\" method"
        );

        foreach ($incorrectValues as $value)
        {
            try
            {
                $valuePrintable = var_export($value, true);
                $set->push($value);
                self::fail("Expect \"$exceptionName\" exception in \"$className\" on seting incorrect value \"$valuePrintable\"");
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
     * @depends readWriteValue
     * @throws
     ************************************************************************/
    public function clearingOperations() : void
    {
        $set                = $this->createSetObject();
        $className          = static::getSetClassName();
        $correctValues      = static::getCorrectDataValues();
        $randomCorrectValue = $correctValues[array_rand($correctValues)];

        foreach ($correctValues as $value)
        {
            $set->push($value);
        }

        $set->delete($randomCorrectValue);
        self::assertEquals
        (
            count($correctValues) - 1,
            $set->count(),
            "Expect get count less by one after delete one item in \"$className\""
        );

        $set->clear();
        self::assertTrue
        (
            $set->isEmpty(),
            "\"$className\" is not empty after call \"clear\" method"
        );
        self::assertEquals
        (
            0,
            $set->count(),
            "\"$className\" values count is not zero after call \"clear\" method"
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