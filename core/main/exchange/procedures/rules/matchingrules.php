<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Participant fields matching rules.
 * Describes matches between same fields of different participants
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class MatchingRules implements Rules
{
    private $aliases = [];
    /** **********************************************************************
     * attach participant field alias
     *
     * @param   Participant $participant        participant
     * @param   string      $fieldName          field name
     * @param   string      $alias              field alias
     ************************************************************************/
    public function attachFieldAlias(Participant $participant, string $fieldName, string $alias) : void
    {
        if (strlen($fieldName) > 0 || strlen($alias) > 0)
        {
            $fieldFullName = $this->getFieldFullName($participant, $alias);
            $this->aliases[$fieldFullName] = $fieldName;
        }
    }
    /** **********************************************************************
     * get participant field by alias
     *
     * @param   Participant $participant        participant
     * @param   string      $alias              participant field alias
     * @return  string|null                     participant field
     ************************************************************************/
    public function getFieldByAlias(Participant $participant, string $alias) : ?string
    {
        $fieldFullName = $this->getFieldFullName($participant, $alias);

        return array_key_exists($fieldFullName, $this->aliases)
            ? $this->aliases[$fieldFullName]
            : null;
    }
    /** **********************************************************************
     * drop participant field alias
     *
     * @param   Participant $participant        participant
     * @param   string      $alias              participant field alias
     ************************************************************************/
    public function dropFieldAlias(Participant $participant, string $alias) : void
    {
        $fieldFullName = $this->getFieldFullName($participant, $alias);

        if (array_key_exists($fieldFullName, $this->aliases))
        {
            unset($this->aliases[$fieldFullName]);
        }
    }
    /** **********************************************************************
     * get item index
     *
     * @param   Participant $participant        participant
     * @param   string      $alias              field alias
     * @return  string                          item index
     ************************************************************************/
    private function getFieldFullName(Participant $participant, string $alias) : string
    {
        return get_class($participant).'@'.$alias;
    }
}