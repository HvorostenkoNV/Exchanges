<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Fields;

use
    InvalidArgumentException,
    Main\Data\Map,
    Main\Exchange\Participants\Fields\FieldsSet as ParticipantsFieldsSet,
    Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Procedure field class
 * Display object as set of different participants fields like ONE procedure field
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
class Field
{
    private
        $procedure          = null,
        $params             = null,
        $participantsFields = [];
    /** **********************************************************************
     * construct
     *
     * @param   Procedure               $procedure          procedure
     * @param   Map                     $params             field params
     * @param   ParticipantsFieldsSet   $participantsFields participants fields
     * @throws  InvalidArgumentException                    incorrect params
     ************************************************************************/
    public function __construct(Procedure $procedure, Map $params, ParticipantsFieldsSet $participantsFields)
    {
        try
        {
            if ($participantsFields->count() <= 0)
            {
                throw new InvalidArgumentException('participants fields set is empty');
            }

            $this->procedure            = $procedure;
            $this->params               = $this->validateParams($params);
            $this->participantsFields   = $participantsFields;
        }
        catch (InvalidArgumentException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get field procedure
     *
     * @return  Procedure                                   procedure
     ************************************************************************/
    public function getProcedure() : Procedure
    {
        return $this->procedure;
    }
    /** **********************************************************************
     * get participants fields
     *
     * @return  ParticipantsFieldsSet                       participants fields
     ************************************************************************/
    public function getParticipantsFields() : ParticipantsFieldsSet
    {
        $this->participantsFields->rewind();
        return $this->participantsFields;
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
        $id = $params->hasKey('id') ? $params->get('id') : 0;

        if (!is_int($id) || $id <= 0)
        {
            throw new InvalidArgumentException('"id" param have to be not empty integer');
        }

        return $params;
    }
}