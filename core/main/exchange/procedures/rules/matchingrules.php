<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures\Rules;

use
    InvalidArgumentException,
    Main\Exchange\Participants\Participant;
/** ***********************************************************************************************
 * Procedures matching rules, describes matches between same fields of different participants and items matching rules
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
class MatchingRules implements Rules
{
    private
        $participants           = [],
        $systemFieldsMap        = [],
        $participantsFieldsMap  = [],
        $matchingRules          = [];
    /** **********************************************************************
     * construct
     * @param   string[]    $participants           work participants array
     * @throws  InvalidArgumentException            participants array is not array of class names of Participant
     ************************************************************************/
    public function __construct(array $participants = [])
    {
        if (count($participants) <= 0)
            throw new InvalidArgumentException('Empty array is geted');

        foreach ($participants as $participantClassName)
            if
            (
                !is_string($participantClassName)       ||
                !class_exists($participantClassName)    ||
                !is_subclass_of($participantClassName, Participant::class)
            )
                throw new InvalidArgumentException('Expect get array of class names of '.Participant::class);

        $this->participants = $participants;
    }
    /** **********************************************************************
     * construct
     * @return  string[]    $participants           work participants array
     ************************************************************************/
    public function getParticipants() : array
    {
        return $this->participants;
    }
    /** **********************************************************************
     * attach participant field
     * @param   string  $systemField                system field name
     * @param   string  $participant                participant class name
     * @param   string  $field                      participant field name
     * @throws  InvalidArgumentException            params error
     ************************************************************************/
    public function attachParticipantField(string $systemField, string $participant, string $field) : void
    {
        $participantFieldKey    = $participant.'@'.$field;
        $systemParticipantKey   = $systemField.'@'.$participant;

        if (strlen($systemField) <= 0)
            throw new InvalidArgumentException('System field param is empty');
        if (!in_array($participant, $this->participants))
            throw new InvalidArgumentException('Participant field is undefined');
        if (strlen($field) <= 0)
            throw new InvalidArgumentException('Participant field param is empty');
        if
        (
            array_key_exists($participantFieldKey, $this->participantsFieldsMap) &&
            $this->participantsFieldsMap[$participantFieldKey] != $systemParticipantKey
        )
            throw new InvalidArgumentException('Field "'.$field.'" for participant "'.$participant.'" already attached for system field "'.$systemField.'"');

        $this->systemFieldsMap[$systemParticipantKey]       = $field;
        $this->participantsFieldsMap[$participantFieldKey]  = $systemField;
    }
    /** **********************************************************************
     * find participant field name
     * @param   string  $systemField                system field name
     * @param   string  $participant                participant class name
     * @return  string|NULL                         participant field name
     ************************************************************************/
    public function findParticipantField(string $systemField, string $participant) : ?string
    {
        $systemParticipantKey = $systemField.'@'.$participant;
        return array_key_exists($systemParticipantKey, $this->systemFieldsMap)
            ? $this->systemFieldsMap[$systemParticipantKey]
            : NULL;
    }
    /** **********************************************************************
     * find system field name
     * @param   string  $participant                participant class name
     * @param   string  $field                      participant field name
     * @return  string|NULL                         system field name
     ************************************************************************/
    public function findSystemField(string $participant, string $field) : ?string
    {
        $participantFieldKey = $participant.'@'.$field;
        return array_key_exists($participantFieldKey, $this->participantsFieldsMap)
            ? $this->participantsFieldsMap[$participantFieldKey]
            : NULL;
    }
    /** **********************************************************************
     * detach participant field
     * @param   string  $systemField                system field name
     * @param   string  $participant                participant class name
     ************************************************************************/
    public function detachParticipantField(string $systemField, string $participant) : void
    {
        $systemParticipantKey   = $systemField.'@'.$participant;
        $field                  = array_key_exists($systemParticipantKey, $this->systemFieldsMap)
            ? $this->systemFieldsMap[$systemParticipantKey]
            : '';
        $participantFieldKey    = $participant.'@'.$field;

        unset($this->systemFieldsMap[$systemParticipantKey]);
        unset($this->participantsFieldsMap[$participantFieldKey]);
    }
    /** **********************************************************************
     * add matching rule between two participants
     * @param   string      $firstParticipant       first participant class name
     * @param   string      $secondParticipant      second participant class name
     * @param   string[]    $systemFields           array of system fields
     * @throws  InvalidArgumentException            incorrect params
     ************************************************************************/
    public function addMatchingRule(string $firstParticipant, string $secondParticipant, array $systemFields) : void
    {
        $ruleKey = $firstParticipant.'@'.$secondParticipant;

        if (!in_array($firstParticipant, $this->participants))
            throw new InvalidArgumentException('"'.$firstParticipant.'" participant is not undefined');
        if (!in_array($secondParticipant, $this->participants))
            throw new InvalidArgumentException('"'.$secondParticipant.'" participant is not undefined');

        if (!array_key_exists($ruleKey, $this->matchingRules))
            $this->matchingRules[$ruleKey] = new MatchingRulesQueue();

        try
        {
            $this->matchingRules[$ruleKey]->push($systemFields);
        }
        catch (InvalidArgumentException $error)
        {
            throw $error;
        }
    }
    /** **********************************************************************
     * get matching rules between two participants
     * @param   string      $firstParticipant       first participant class name
     * @param   string      $secondParticipant      second participant class name
     * @return  MatchingRulesQueue|NULL             queue of rules or NULL
     * @throws  InvalidArgumentException            incorrect params
     ************************************************************************/
    public function getMatchingRules(string $firstParticipant, string $secondParticipant) : ?MatchingRulesQueue
    {
        $ruleKey = $firstParticipant.'@'.$secondParticipant;
        return array_key_exists($ruleKey, $this->matchingRules)
            ? $this->matchingRules[$ruleKey]
            : NULL;
    }
    /** **********************************************************************
     * clear matching rules between two participants
     * @param   string      $firstParticipant       first participant class name
     * @param   string      $secondParticipant      second participant class name
     * @throws  InvalidArgumentException            incorrect params
     ************************************************************************/
    public function clearMatchingRules(string $firstParticipant, string $secondParticipant) : void
    {
        $ruleKey = $firstParticipant.'@'.$secondParticipant;
        if (array_key_exists($ruleKey, $this->matchingRules))
            unset($this->matchingRules[$ruleKey]);
    }
}