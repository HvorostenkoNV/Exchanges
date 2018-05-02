<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Participant data combining rules.
 * Describes different participant data combining process
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class CombiningRules implements Rules
{
    private
        $priorities         = [],
        $emptyPriorities    = [];
    /** **********************************************************************
     * attach participant field priority
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     * @param   int         $priority           field priority
     ************************************************************************/
    public function attachFieldPriority(Participant $participant, string $fieldName, int $priority) : void
    {
        if (strlen($fieldName) > 0 && $priority > 0)
        {
            $fieldFullName = $this->getFieldFullName($participant, $fieldName);
            $this->priorities[$fieldFullName] = $priority;
        }
    }
    /** **********************************************************************
     * get participant field priority
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     * @return  int                             field priority
     ************************************************************************/
    public function getFieldPriority(Participant $participant, string $fieldName) : int
    {
        $fieldFullName = $this->getFieldFullName($participant, $fieldName);

        return array_key_exists($fieldFullName, $this->priorities)
            ? $this->priorities[$fieldFullName]
            : 0;
    }
    /** **********************************************************************
     * drop participant field priority
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     ************************************************************************/
    public function dropFieldPriority(Participant $participant, string $fieldName) : void
    {
        $fieldFullName = $this->getFieldFullName($participant, $fieldName);

        if (array_key_exists($fieldFullName, $this->priorities))
        {
            unset($this->priorities[$fieldFullName]);
        }
    }
    /** **********************************************************************
     * attach participant field has priority even if its value is empty
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     ************************************************************************/
    public function attachEmptyFieldHasPriority(Participant $participant, string $fieldName) : void
    {
        if (strlen($fieldName) > 0)
        {
            $fieldFullName = $this->getFieldFullName($participant, $fieldName);
            $this->emptyPriorities[$fieldFullName] = null;
        }
    }
    /** **********************************************************************
     * check participant field has priority even if its value is empty
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     * @return  bool                            empty field has priority
     ************************************************************************/
    public function checkEmptyFieldHasPriority(Participant $participant, string $fieldName) : bool
    {
        $fieldFullName = $this->getFieldFullName($participant, $fieldName);
        return array_key_exists($fieldFullName, $this->emptyPriorities);
    }
    /** **********************************************************************
     * detach participant field has priority even if its value is empty
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     ************************************************************************/
    public function detachEmptyFieldHasPriority(Participant $participant, string $fieldName) : void
    {
        $fieldFullName = $this->getFieldFullName($participant, $fieldName);

        if (array_key_exists($fieldFullName, $this->emptyPriorities))
        {
            unset($this->emptyPriorities[$fieldFullName]);
        }
    }
    /** **********************************************************************
     * get item index
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     * @return  string                          item index
     ************************************************************************/
    private function getFieldFullName(Participant $participant, string $fieldName) : string
    {
        return get_class($participant).'@'.$fieldName;
    }
}