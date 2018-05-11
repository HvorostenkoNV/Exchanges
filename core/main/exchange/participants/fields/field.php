<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Fields;

use
    InvalidArgumentException,
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager;
/** ***********************************************************************************************
 * Participant field
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class Field
{
    private
        $params = null,
        $type   = null;
    /** **********************************************************************
     * construct
     *
     * @param   MapData     $params         field params
     * @throws  InvalidArgumentException    incorrect params
     ************************************************************************/
    final public function __construct(MapData $params)
    {
        try
        {
            $this->params   = $this->validateParams($params);
            $this->type     = FieldsTypesManager::getField($this->params->get('type'));
        }
        catch (InvalidArgumentException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get field param
     *
     * @param   string $param               param name
     * @return  mixed                       param value
     ************************************************************************/
    final public function getParam(string $param)
    {
        return $this->params->hasKey($param)
            ? $this->params->get($param)
            : null;
    }
    /** **********************************************************************
     * validate field params
     *
     * @param   MapData $params             field params
     * @return  MapData                     field validated params
     * @throws  InvalidArgumentException    incorrect params
     ************************************************************************/
    protected function validateParams(MapData $params) : MapData
    {
        $type       = $params->hasKey('type')       ? $params->get('type')      : '';
        $name       = $params->hasKey('name')       ? $params->get('name')      : '';
        $required   = $params->hasKey('required')   ? $params->get('required')  : false;

        if (!is_string($type) || strlen($type) <= 0)
        {
            throw new InvalidArgumentException('"type" param have to be not empty string');
        }
        if (!is_string($name) || strlen($name) <= 0)
        {
            throw new InvalidArgumentException('"name" param have to be not empty string');
        }
        if (!is_bool($required))
        {
            throw new InvalidArgumentException('"required" param have to be boolean value');
        }

        $params->set('required', $required);

        return $params;
    }
}