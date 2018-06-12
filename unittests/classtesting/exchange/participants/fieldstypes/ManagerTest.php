<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\FieldsTypes;

use
    InvalidArgumentException,
    UnitTests\AbstractTestCase,
    Main\Helpers\DB,
    Main\Exchange\Participants\FieldsTypes\Manager;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\FieldsTypes\Manager classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ManagerTest extends AbstractTestCase
{

    private static $fieldsTypesTable = 'fields_types';
    /** **********************************************************************
     * check available fields types
     *
     * @test
     * @throws
     ************************************************************************/
    public function availableFieldsTypes() : void
    {
        $db                     = DB::getInstance();
        $table                  = self::$fieldsTypesTable;
        $queryResult            = $db->query("SELECT * FROM $table");
        $fieldsTypesFromDb      = [];
        $fieldsTypesFromClass   = Manager::getAvailableFieldsTypes();

        while (!$queryResult->isEmpty())
        {
            $fieldsTypesFromDb[] = $queryResult->pop()->get('CODE');
        }

        sort($fieldsTypesFromDb);
        sort($fieldsTypesFromClass);

        self::assertEquals
        (
            $fieldsTypesFromDb,
            $fieldsTypesFromClass,
            'Expect get available fields types same as in DB'
        );
        self::assertTrue
        (
            $fieldsTypesFromClass > 0,
            'Expect get not empty available fields types array'
        );
    }
    /** **********************************************************************
     * check fields constructing process
     *
     * @test
     * @depends availableFieldsTypes
     * @throws
     ************************************************************************/
    public function fieldsConstructing() : void
    {
        foreach (Manager::getAvailableFieldsTypes() as $type)
        {
            try
            {
                Manager::getField($type);
                self::assertTrue(true);
            }
            catch (InvalidArgumentException $exception)
            {
                $error = $exception->getMessage();
                self::fail("Creating participant field type error: $error");
            }
        }
    }
    /** **********************************************************************
     * check incorrect fields constructing process
     *
     * @test
     * @depends fieldsConstructing
     * @throws
     ************************************************************************/
    public function incorrectFieldsConstructing() : void
    {
        $availableFieldsTypes   = Manager::getAvailableFieldsTypes();
        $incorrectFieldType     = 'incorrectFieldType';
        $exceptionName          = InvalidArgumentException::class;

        while (in_array($incorrectFieldType, $availableFieldsTypes))
        {
            $incorrectFieldType .= '!';
        }

        try
        {
            Manager::getField($incorrectFieldType);
            self::fail("Expect $exceptionName exception on getting field by incorrect field type");
        }
        catch (InvalidArgumentException $exception)
        {
            self::assertTrue(true);
        }
    }
}