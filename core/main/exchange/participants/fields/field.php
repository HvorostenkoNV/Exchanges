<?php
declare(strict_types=1);

namespace Main\Exchange\Participants\Fields;

use
    InvalidArgumentException,
    Main\Data\Map,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\FieldsTypes\Manager  as FieldsTypesManager,
    Main\Exchange\Participants\FieldsTypes\Field    as FieldType;
/** ***********************************************************************************************
 * Participant field
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
final class Field
{
    private
        $participant    = null,
        $params         = null,
        $type           = null;
    /** **********************************************************************
     * construct
     *
     * @param   Participant $participant    participant
     * @param   Map         $params         field params
     * @throws  InvalidArgumentException    incorrect params
     ************************************************************************/
    public function __construct(Participant $participant, Map $params)
    {
        try
        {
            $this->participant  = $participant;
            $this->params       = $this->validateParams($params);
            $this->type         = FieldsTypesManager::getField($this->params->get('type'));
        }
        catch (InvalidArgumentException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get field participant
     *
     * @return  Participant                 participant
     ************************************************************************/
    public function getParticipant() : Participant
    {
        return $this->participant;
    }
    /** **********************************************************************
     * get field type
     *
     * @return  FieldType                   field type object
     ************************************************************************/
    public function getFieldType() : FieldType
    {
        return $this->type;
    }
    /** **********************************************************************
     * get field param
     *
     * @param   string $param               param name
     * @return  mixed                       param value
     ************************************************************************/
    public function getParam(string $param)
    {
        return $this->params->hasKey($param)
            ? $this->params->get($param)
            : null;
    }
    /** **********************************************************************
     * validate field params
     *
     * @param   Map $params                 field params
     * @return  Map                         field validated params
     * @throws  InvalidArgumentException    incorrect params
     ************************************************************************/
    private function validateParams(Map $params) : Map
    {
        $id         = $params->hasKey('id')         ? $params->get('id')        : 0;
        $name       = $params->hasKey('name')       ? $params->get('name')      : '';
        $type       = $params->hasKey('type')       ? $params->get('type')      : '';
        $required   = $params->hasKey('required')   ? $params->get('required')  : false;

        if (!is_int($id) || $id <= 0)
        {
            throw new InvalidArgumentException('"id" param have to be not empty integer');
        }
        if (!is_string($name) || strlen($name) <= 0)
        {
            throw new InvalidArgumentException('"name" param have to be not empty string');
        }
        if (!is_string($type) || strlen($type) <= 0)
        {
            throw new InvalidArgumentException('"type" param have to be not empty string');
        }
        if (!is_bool($required))
        {
            throw new InvalidArgumentException('"required" param have to be boolean value');
        }

        $params->set('required', $required);

        return $params;
    }
}