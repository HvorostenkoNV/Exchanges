<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Data;

use
    RuntimeException,
    Main\Helpers\DB;
/** ***********************************************************************************************
 * Participant field class
 * describes participant field
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class Field
{
    private
        $name       = '',
        $required   = false,
        $type       = '';
    private static
        $availableTypes = [],
        $defaultType    = '',
        $typesGeted     = false;
    /** **********************************************************************
     * get available fields types
     *
     * @return  string[]                    available fields types
     ************************************************************************/
    public static function getAvailableTypes() : array
    {
        self::calcFieldsTypesInfo();
        return self::$availableTypes;
    }
    /** **********************************************************************
     * get default fields type
     *
     * @return  string                      default fields type
     ************************************************************************/
    public static function getDefaultType() : string
    {
        self::calcFieldsTypesInfo();
        return self::$defaultType;
    }
    /** **********************************************************************
     * calc available fields types
     ************************************************************************/
    private static function calcFieldsTypesInfo() : void
    {
        if (self::$typesGeted)
        {
            return;
        }
        self::$typesGeted = true;

        try
        {
            $db             = DB::getInstance();
            $queryResult    = $db->query('SELECT CODE, IS_DEFAULT FROM participants_fields_types');

            while (!$queryResult->isEmpty())
            {
                $item       = $queryResult->pop();
                $type       = $item->get('CODE');
                $isDefault  = $item->get('IS_DEFAULT');

                self::$availableTypes[] = $type;
                if ($isDefault === 'Y')
                {
                    self::$defaultType = $type;
                }
            }
        }
        catch (RuntimeException $exception)
        {

        }
    }
    /** **********************************************************************
     * construct
     ************************************************************************/
    public function __construct()
    {
        $this->type = self::getDefaultType();
    }
    /** **********************************************************************
     * set field name
     *
     * @param   string  $name               field name
     ************************************************************************/
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    /** **********************************************************************
     * get field name
     *
     * @return  string                      field name
     ************************************************************************/
    public function getName() : string
    {
        return $this->name;
    }
    /** **********************************************************************
     * mark field as required
     *
     * @param   bool    $value              required value
     ************************************************************************/
    public function setRequired(bool $value) : void
    {
        $this->required = $value;
    }
    /** **********************************************************************
     * check field is required
     *
     * @return  bool                        field is required
     ************************************************************************/
    public function isRequired() : bool
    {
        return $this->required;
    }
    /** **********************************************************************
     * set field type
     *
     * @param   string $type                field type
     ************************************************************************/
    public function setType(string $type) : void
    {
        $this->type = in_array($type, self::getAvailableTypes())
            ? $type
            : self::getDefaultType();
    }
    /** **********************************************************************
     * get field type
     *
     * @return  string                      field type
     ************************************************************************/
    public function getType() : string
    {
        return $this->type;
    }
}