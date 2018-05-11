<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    RuntimeException,
    InvalidArgumentException,
    Main\Data\Queue;
/** ***********************************************************************************************
 * Parent class for testing Main\Data\Queue classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class QueueDataClass extends ExchangeTestCase
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Queue class name
     ************************************************************************/
    abstract public static function getQueueClassName() : string;
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
        $queue      = $this->createQueueObject();
        $className  = static::getQueueClassName();

        self::assertTrue
        (
            $queue->isEmpty(),
            "New $className object is not empty"
        );
        self::assertEquals
        (
            0,
            $queue->count(),
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
        $queue      = $this->createQueueObject();
        $values     = static::getCorrectDataValues();
        $className  = static::getQueueClassName();

        if (count($values) <= 0)
        {
            self::markTestSkipped("No \"correct\" values for testing $className class");
            return;
        }

        foreach ($values as $value)
        {
            $queue->push($value);
        }

        self::assertFalse
        (
            $queue->isEmpty(),
            "Filled $className is empty"
        );
        self::assertEquals
        (
            count($values),
            $queue->count(),
            "Filled $className values count is not equal items count put"
        );

        foreach ($values as $index => $value)
        {
            self::assertEquals
            (
                $value,
                $queue->pop(),
                "Value put in $className before not equals received"
            );
            self::assertEquals
            (
                count($values) - $index - 1,
                $queue->count(),
                "$className values count is not equal expected after pop"
            );
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
        $queue              = $this->createQueueObject();
        $incorrectValues    = static::getIncorrectDataValues();
        $className          = static::getQueueClassName();

        if (count($incorrectValues) <= 0)
        {
            self::markTestSkipped("No \"incorrect\" values for testing $className class");
            return;
        }

        try
        {
            $exceptionName = InvalidArgumentException::class;
            $queue->pop();
            self::fail("Expect $exceptionName exception with pop on empty $className");
        }
        catch (RuntimeException $error)
        {
            self::assertTrue(true);
        }

        foreach ($incorrectValues as $value)
        {
            try
            {
                $exceptionName  = InvalidArgumentException::class;
                $valuePrintable = var_export($value, true);

                $queue->push($value);
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
        $queue      = $this->createQueueObject();
        $className  = static::getQueueClassName();

        foreach (static::getCorrectDataValues() as $value)
        {
            $queue->push($value);
        }
        $queue->clear();

        self::assertTrue
        (
            $queue->isEmpty(),
            "$className is not empty after call \"clear\" method"
        );
        self::assertEquals
        (
            0,
            $queue->count(),
            "$className values count is not zero after call \"clear\" method"
        );
    }
    /** **********************************************************************
     * get new queue object
     *
     * @return  Queue                       new queue object
     ************************************************************************/
    private function createQueueObject() : Queue
    {
        $className = static::getQueueClassName();

        return new $className;
    }
}