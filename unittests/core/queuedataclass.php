<?php
declare(strict_types=1);

use Main\Data\Queue;
/** ***********************************************************************************************
 * Parent class for testing Main\Data\Queue classes
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class QueueDataClass extends ExchangeTestCase
{
    protected static $queueClassName = '';
    /** **********************************************************************
     * check empty object
     * @test
     ************************************************************************/
    public function emptyObject() : void
    {
        $queue = self::createQueueObject();

        self::assertTrue
        (
            $queue->isEmpty(),
            'New '.static::$queueClassName.' object is not empty'
        );
        self::assertEquals
        (
            0, $queue->count(),
            'New '.static::$queueClassName.' object values count is not zero'
        );
    }
    /** **********************************************************************
     * check read/write operations
     * @test
     * @depends emptyObject
     ************************************************************************/
    public function readWriteOperations() : void
    {
        $queue  = self::createQueueObject();
        $values = static::getCorrectValues();

        if (count($values) <= 0)
        {
            self::assertTrue(true);
            return;
        }

        foreach ($values as $value)
            $queue->push($value);

        self::assertFalse
        (
            $queue->isEmpty(),
            'Filled '.static::$queueClassName.' is empty'
        );
        self::assertEquals
        (
            count($values), $queue->count(),
            'Filled '.static::$queueClassName.' values count is not equal items count put'
        );

        foreach ($values as $index => $value)
        {
            self::assertEquals
            (
                $value, $queue->pop(),
                'Value put in '.static::$queueClassName.' before not equals received'
            );
            self::assertEquals
            (
                count($values) - $index - 1, $queue->count(),
                static::$queueClassName.' values count is not equal expected after pop'
            );
        }
    }
    /** **********************************************************************
     * check incorrect read/write operations
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function incorrectReadWriteOperations() : void
    {
        $queue              = self::createQueueObject();
        $incorrectValues    = static::getIncorrectValues();

        try
        {
            $queue->pop();
            self::fail('Expect '.RuntimeException::class.' exception with pop on empty '.static::$queueClassName);
        }
        catch (RuntimeException $error)
        {
            self::assertTrue(true);
        }

        if (count($incorrectValues))
            foreach ($incorrectValues as $value)
            {
                try
                {
                    $queue->push($value);
                    self::fail('Expect '.InvalidArgumentException::class.' exception in '.static::$queueClassName.' on push incorrect value '.var_export($value, true));
                }
                catch (InvalidArgumentException $error)
                {
                    self::assertTrue(true);
                }
            }
    }
    /** **********************************************************************
     * check clearing operations
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function clearingOperations() : void
    {
        $queue = self::createQueueObject();

        foreach (static::getCorrectValues() as $value)
            $queue->push($value);

        $queue->clear();

        self::assertTrue
        (
            $queue->isEmpty(),
            static::$queueClassName.' is not empty after call "clear" method'
        );
        self::assertEquals
        (
            0, $queue->count(),
            static::$queueClassName.' values count is not zero after call "clear" method'
        );
    }
    /** **********************************************************************
     * get correct data
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        return [];
    }
    /** **********************************************************************
     * get incorrect values
     * @return  array                   incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        return [];
    }
    /** **********************************************************************
     * get new queue object
     * @return  Queue                   new queue object
     ************************************************************************/
    final protected static function createQueueObject() : Queue
    {
        return new static::$queueClassName;
    }
}