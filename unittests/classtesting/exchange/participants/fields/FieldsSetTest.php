<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    UnitTests\ClassTesting\Data\SetDataAbstractTest,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\Fields\FieldsSet;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\FieldsSet class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldsSetTest extends SetDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return FieldsSet::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        return array_values(self::getRandomFieldsArray());
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            new MapData,
            new FieldsSet
        ];
    }
    /** **********************************************************************
     * check find field operation
     *
     * @test
     * @throws
     ************************************************************************/
    public function findField() : void
    {
        $set            = new FieldsSet;
        $fields         = self::getRandomFieldsArray();
        $fieldClassName = Field::class;

        foreach ($fields as $field)
        {
            $set->push($field);
        }

        $set->rewind();
        for ($index = count($fields) - 2;$index > 0; $index--)
        {
            $set->next();
        }

        $currentKey = $set->key();
        foreach ($fields as $name => $field)
        {
            self::assertEquals
            (
                $field,
                $set->findField($name),
                "Expect get same \"$fieldClassName\" by name as was seted before"
            );
            self::assertEquals
            (
                $currentKey,
                $set->key(),
                'Expect current key is same as before call "findField" method'
            );
        }
    }
    /** **********************************************************************
     * check find field operation by unknown field name
     *
     * @test
     * @throws
     ************************************************************************/
    public function findFieldByUnknownKey() : void
    {
        $set                = new FieldsSet;
        $fields             = self::getRandomFieldsArray();
        $unknownFieldName   = 'unknownFieldName';

        while (array_key_exists($unknownFieldName, $fields))
        {
            $unknownFieldName .= '!';
        }

        foreach ($fields as $field)
        {
            $set->push($field);
        }

        self::assertNull
        (
            $set->findField($unknownFieldName),
            'Expect null on trying to find field by unknown field name'
        );
    }
    /** **********************************************************************
     * get random fields array
     *
     * @return  Field[]                     fields array
     ************************************************************************/
    private static function getRandomFieldsArray() : array
    {
        $result                 = [];
        $availableFieldsTypes   = FieldsTypesManager::getAvailableFieldsTypes();

        for ($index = 1; $index <= 10; $index++)
        {
            $fieldParams    = new MapData;
            $fieldName      = "fieldName-$index";

            $fieldParams->set('name', $fieldName);
            $fieldParams->set('type', $availableFieldsTypes[array_rand($availableFieldsTypes)]);

            $result[$fieldName] = new Field($fieldParams);
        }

        return $result;
    }
}