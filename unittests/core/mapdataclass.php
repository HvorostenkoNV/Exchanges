<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    InvalidArgumentException,
    Main\Data\Map;
/** ***********************************************************************************************
 * Parent class for testing Main\Data\Map classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class MapDataClass extends ExchangeTestCase
{
    /** **********************************************************************
     * get new map object
     *
     * @param   array   $data               start data
     * @return  Map                         new map object
     ************************************************************************/
    private static function createMapObject(array $data = []) : Map
    {
        $className = static::getMapClassName();

        return count($data) > 0 ? new $className($data) : new $className;
    }
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    abstract protected static function getMapClassName() : string;
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    abstract protected static function getCorrectData() : array;
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect keys
     ************************************************************************/
    abstract protected static function getIncorrectKeys() : array;
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect values
     ************************************************************************/
    abstract protected static function getIncorrectValues() : array;
    /** **********************************************************************
     * check empty object
     *
     * @test
     ************************************************************************/
    public function emptyObject() : void
    {
        $map        = static::createMapObject();
        $className  = static::getMapClassName();

        self::assertTrue
        (
            $map->isEmpty(),
            "New $className object is not empty"
        );
        self::assertEquals
        (
            0,
            $map->count(),
            "New $className object values count is not zero"
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
        $map        = static::createMapObject();
        $values     = static::getCorrectData();
        $className  = static::getMapClassName();

        if (count($values) <= 0)
        {
            self::assertTrue(true);
            return;
        }

        foreach ($values as $index => $value)
        {
            $map->set($index, $value);
        }

        self::assertFalse
        (
            $map->isEmpty(),
            "Filled $className is empty"
        );
        self::assertEquals
        (
            count($values),
            $map->count(),
            "Filled $className values count is not equal items count put"
        );

        foreach ($values as $index => $value)
        {
            self::assertEquals
            (
                $value,
                $map->get($index),
                "Value put before into $className not equals received"
            );
            self::assertTrue
            (
                $map->hasKey($index),
                "Key seted before into $className not found"
            );
            self::assertTrue
            (
                $map->hasValue($value),
                "Value put before into $className not found"
            );
        }

        self::assertEquals
        (
            array_keys($values),
            $map->getKeys(),
            "Received keys from $className is not equal put before"
        );
    }
    /** **********************************************************************
     * check incorrect read/write operations
     *
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function incorrectReadWriteOperations() : void
    {
        $map                = static::createMapObject();
        $className          = static::getMapClassName();
        $correctData        = static::getCorrectData();
        $setedKeys          = $map->getKeys();
        $incorrectKeys      = static::getIncorrectKeys();
        $incorrectValues    = static::getIncorrectValues();
        $unknownKey         = 'unknownKey';
        $correctKey         = count($correctData) > 0   ? array_rand($correctData)  : null;
        $correctValue       = $correctKey               ? $correctData[$correctKey] : null;

        while (in_array($unknownKey, $setedKeys))
        {
            $unknownKey .= '1';
        }

        self::assertNull
        (
            $map->get($unknownKey),
            "Received value in $className by incorrect key is not null"
        );

        if (count($incorrectKeys) > 0 && $correctValue)
        {
            foreach ($incorrectKeys as $key)
            {
                try
                {
                    $exceptionName  = InvalidArgumentException::class;
                    $printValue     = var_export($key, true);

                    $map->set($key, $correctValue);
                    self::fail("Expect $exceptionName exception in $className on seting value by incorrect key $printValue");
                }
                catch (InvalidArgumentException $error)
                {
                    self::assertTrue(true);
                }
            }
        }

        if (count($incorrectValues) > 0 && $correctKey)
        {
            foreach ($incorrectValues as $value)
            {
                try
                {
                    $exceptionName  = InvalidArgumentException::class;
                    $printValue     = var_export($value, true);

                    $map->set($correctKey, $value);
                    self::fail("Expect $exceptionName exception in $className on seting value by incorrect value $printValue");
                }
                catch (InvalidArgumentException $error)
                {
                    self::assertTrue(true);
                }
            }
        }
    }
    /** **********************************************************************
     * check alternative create syntax
     *
     * @test
     * @depends readWriteOperations
     ************************************************************************/
    public function alternativeCreateSyntax() : void
    {
        $values     = static::getCorrectData();
        $map        = static::createMapObject($values);
        $className  = static::getMapClassName();

        self::assertFalse
        (
            $map->isEmpty(),
            "$className created by array is empty"
        );
        self::assertEquals
        (
            count($values),
            $map->count(),
            "$className values count is not equal array count created on"
        );

        foreach ($values as $index => $value)
        {
            self::assertEquals
            (
                $value,
                $map->get($index),
                "Value form array $className created on not equals received"
            );
        }
    }
    /** **********************************************************************
     * check clearing operations
     *
     * @test
     * @depends alternativeCreateSyntax
     ************************************************************************/
    public function clearingOperations() : void
    {
        $map        = static::createMapObject(static::getCorrectData());
        $className  = static::getMapClassName();
        $keys       = $map->getKeys();

        $map->delete($keys[array_rand($keys)]);
        self::assertEquals
        (
            count($keys) - 1,
            $map->count(),
            "$className values count not less by one after delete one item"
        );

        $map->clear();
        self::assertTrue
        (
            $map->isEmpty(),
            "$className is not empty after call \"clear\" method"
        );
        self::assertEquals
        (
            0,
            $map->count(),
            "$className values count is not zero after call \"clear\" method"
        );
    }
}