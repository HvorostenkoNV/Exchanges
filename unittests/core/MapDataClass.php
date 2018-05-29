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
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    abstract public static function getMapClassName() : string;
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data
     ************************************************************************/
    abstract public static function getCorrectData() : array;
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect data keys
     ************************************************************************/
    abstract public static function getIncorrectDataKeys() : array;
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
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
        $map            = $this->createMapObject();
        $className      = static::getMapClassName();
        $correctData    = static::getCorrectData();

        if (count($correctData) <= 0)
        {
            self::markTestSkipped("No \"correct\" data for testing \"$className\" class");
            return;
        }

        self::assertNull
        (
            $map->get('unknownKey'),
            "Expect get null on getting value by unknown key in \"$className\""
        );

        foreach ($correctData as $values)
        {
            $map->set($values[0], $values[1]);

            self::assertEquals
            (
                $values[1],
                $map->get($values[0]),
                "Value put before into \"$className\" not equals received"
            );
            self::assertTrue
            (
                $map->hasKey($values[0]),
                "Key seted before into \"$className\" not found"
            );
            self::assertTrue
            (
                $map->hasValue($values[1]),
                "Value put before into \"$className\" not found"
            );
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
        $map            = $this->createMapObject();
        $className      = static::getMapClassName();
        $correctData    = static::getCorrectData();
        $uniqueKeys     = [];

        self::assertTrue
        (
            $map->isEmpty(),
            "New \"$className\" object is not empty"
        );
        self::assertEquals
        (
            0,
            $map->count(),
            "New \"$className\" object values count is not zero"
        );

        foreach ($correctData as $values)
        {
            $uniqueKeys[] = $this->convertToString($values[0]);
            $map->set($values[0], $values[1]);
        }
        $uniqueKeys = array_unique($uniqueKeys);

        self::assertFalse
        (
            $map->isEmpty(),
            "Filled \"$className\" is empty"
        );
        self::assertEquals
        (
            count($uniqueKeys),
            $map->count(),
            "Filled \"$className\" values count is not equal items count put"
        );
    }
    /** **********************************************************************
     * check getting keys operation
     *
     * @test
     * @depends readWriteValue
     * @throws
     ************************************************************************/
    public function gettingKeys() : void
    {
        $map                = $this->createMapObject();
        $className          = static::getMapClassName();
        $correctData        = static::getCorrectData();
        $correctDataKeys    = [];

        foreach ($correctData as $values)
        {
            $keyString                      = $this->convertToString($values[0]);
            $correctDataKeys[$keyString]    = $values[0];
            $map->set($values[0], $values[1]);
        }

        self::assertEquals
        (
            array_values($correctDataKeys),
            $map->getKeys(),
            "Received keys from \"$className\" is not equal put before"
        );
    }
    /** **********************************************************************
     * check read/write value operations with incorrect keys
     *
     * @test
     * @depends readWriteValue
     * @throws
     ************************************************************************/
    public function usingIncorrectKeys() : void
    {
        $map            = $this->createMapObject();
        $className      = static::getMapClassName();
        $correctData    = static::getCorrectData();
        $incorrectKeys  = static::getIncorrectDataKeys();
        $correctValue   = $correctData[array_rand($correctData)][1];
        $exceptionName  = InvalidArgumentException::class;

        if (count($incorrectKeys) <= 0)
        {
            self::markTestSkipped("No \"incorrect\" data keys for testing \"$className\" class");
            return;
        }

        foreach ($incorrectKeys as $key)
        {
            try
            {
                $keyPrintable = var_export($key, true);
                $map->set($key, $correctValue);
                self::fail("Expect \"$exceptionName\" exception in \"$className\" on seting value by incorrect key \"$keyPrintable\"");
            }
            catch (InvalidArgumentException $error)
            {
                self::assertTrue(true);
            }
        }
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
        $map                = $this->createMapObject();
        $className          = static::getMapClassName();
        $correctData        = static::getCorrectData();
        $incorrectValues    = static::getIncorrectDataValues();
        $correctKey         = $correctData[array_rand($correctData)][0];
        $exceptionName      = InvalidArgumentException::class;

        if (count($incorrectValues) <= 0)
        {
            self::markTestSkipped("No \"incorrect\" data values for testing \"$className\" class");
            return;
        }

        foreach ($incorrectValues as $value)
        {
            try
            {
                $valuePrintable = var_export($value, true);
                $map->set($correctKey, $value);
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
     * @depends counting
     * @throws
     ************************************************************************/
    public function clearingOperations() : void
    {
        $map        = $this->createMapObject(static::getCorrectData());
        $className  = static::getMapClassName();
        $keys       = $map->getKeys();
        $randKey    = $keys[array_rand($keys)];

        $map->delete($randKey);
        self::assertEquals
        (
            count($keys) - 1,
            $map->count(),
            "Expect get count less by one after delete one item in \"$className\""
        );

        $map->clear();
        self::assertTrue
        (
            $map->isEmpty(),
            "\"$className\" is not empty after call \"clear\" method"
        );
        self::assertEquals
        (
            0,
            $map->count(),
            "\"$className\" values count is not zero after call \"clear\" method"
        );
    }
    /** **********************************************************************
     * get new map object
     *
     * @param   array   $data               start data
     * @return  Map                         new map object
     ************************************************************************/
    private function createMapObject(array $data = []) : Map
    {
        $className  = static::getMapClassName();
        $map        = new $className;

        foreach ($data as $values)
        {
            call_user_func_array([$map, 'set'], [$values[0], $values[1]]);
        }

        return $map;
    }
    /** **********************************************************************
     * convert value to string
     *
     * @param   mixed   $value              value
     * @return  string                      value as string
     ************************************************************************/
    private function convertToString($value) : string
    {
        switch (gettype($value))
        {
            case 'boolean':
                return 'boolean-'.json_encode($value);
            case 'array':
                return json_encode($value);
            case 'object':
                return spl_object_hash($value);
            case 'resource':
                return strval((int) $value);
            case 'NULL':
                return 'null-value';
                break;
            case 'string':
            case 'integer':
            case 'double':
            default:
                return strval($value);
        }
    }
}