<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\Fields;

use
    UnitTests\ClassTesting\Data\SetDataAbstractTest,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantStub,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\Fields\Field         as ParticipantField,
    Main\Exchange\Participants\Fields\FieldsSet     as ParticipantFieldsSet;
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
        return ParticipantFieldsSet::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        return self::getRandomFieldsArray();
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
                new ParticipantFieldsSet
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
        $set            = new ParticipantFieldsSet;
        $fields         = self::getRandomFieldsArray();
        $fieldClassName = ParticipantField::class;

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
        foreach ($fields as $field)
        {
            $fieldName = $field->getParam('name');

            self::assertEquals
            (
                $field,
                $set->findField($fieldName),
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
        $set                = new ParticipantFieldsSet;
        $fields             = self::getRandomFieldsArray();
        $fieldsNames        = [];
        $unknownFieldName   = 'unknownFieldName';

        foreach ($fields as $field)
        {
            $fieldsNames[] = $field->getParam('name');
            $set->push($field);
        }
        while (in_array($unknownFieldName, $fieldsNames))
        {
            $unknownFieldName .= '!';
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
     * @return  ParticipantField[]          fields array
     ************************************************************************/
    private static function getRandomFieldsArray() : array
    {
        $result                 = [];
        $availableFieldsTypes   = FieldsTypesManager::getAvailableFieldsTypes();
        $participant            = new ParticipantStub;

        for ($index = 1; $index <= 10; $index++)
        {
            $fieldParams = new MapData;
            $fieldParams->set('id',     rand(1, getrandmax()));
            $fieldParams->set('name',   "fieldName-$index");
            $fieldParams->set('type',   $availableFieldsTypes[array_rand($availableFieldsTypes)]);
            $result[] = new ParticipantField($participant, $fieldParams);
        }

        return $result;
    }
}