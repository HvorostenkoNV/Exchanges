<?php
declare(strict_types=1);

use
    Main\Exchange\Participants\Users1C,
    Main\Exchange\Participants\UsersAD,
    Main\Exchange\Participants\UsersBitrix,
    Main\Data\MapData,
    Main\Exchange\Procedures\Rules\MatchingRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Rules\MatchingRules class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatchingRulesClassTest extends ExchangeTestCase
{
    private static
        $correctParticipantsArray   = [],
        $incorrectParticipantArrays = [],
        $unusedParticipant          = '',
        $correctSystemFields        = [],
        $incorrectSystemFields      = [],
        $correctFieldsData          = [],
        $incorrectMatchingRules     = [];
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        self::$correctParticipantsArray     =
        [
            Users1C::class,
            UsersAD::class
        ];
        self::$incorrectParticipantArrays   =
        [
            [],
            ['string1', 'string2'],
            [Users1C::class, MatchingRules::class]
        ];
        self::$unusedParticipant            = UsersBitrix::class;
        self::$correctSystemFields          = ['systemField1', 'systemField2', 'systemField3'];
        self::$incorrectSystemFields        = ['systemField3', 'systemField4'];

        foreach (self::$correctSystemFields as $systemField)
        {
            self::$correctFieldsData[$systemField] = [];
            foreach (self::$correctParticipantsArray as $participant)
                self::$correctFieldsData[$systemField][$participant] = 'field'.$systemField.$participant;
        }

        self::$incorrectMatchingRules       =
        [
            ['string', 'string', 1],
            ['string', 'string', new MapData]
        ];
    }
    /** **********************************************************************
     * testing creating object
     * @test
     ************************************************************************/
    public function objectCreating() : void
    {
        $matchingRules = new MatchingRules(self::$correctParticipantsArray);

        self::assertEquals
        (
            self::$correctParticipantsArray, $matchingRules->getParticipants(),
            'Incorrect geted participants array'
        );

        foreach (self::$incorrectParticipantArrays as $constructParam)
        {
            try
            {
                new MatchingRules($constructParam);
                self::fail('Expect '.InvalidArgumentException::class.' on creating '.MatchingRules::class.' object with incorrect participants params');
            }
            catch (InvalidArgumentException $error)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * testing methods related to the matching same fields of different participants
     * @test
     * @depends objectCreating
     ************************************************************************/
    public function fieldsReadWriteOperations() : void
    {
        $matchingRules = new MatchingRules(self::$correctParticipantsArray);

        foreach (self::$correctFieldsData as $systemField => $participantsFields)
            foreach ($participantsFields as $participant => $field)
            {
                $matchingRules->attachParticipantField($systemField, $participant, $field);

                self::assertEquals
                (
                    $field, $matchingRules->findParticipantField($systemField, $participant),
                    'Geted participant field not equal saved'
                );
                self::assertEquals
                (
                    $systemField, $matchingRules->findSystemField($participant, $field),
                    'Geted system field not equal saved'
                );
            }
    }
    /** **********************************************************************
     * testing incorrect using of methods related to the matching same fields of different participants
     * @test
     * @depends fieldsReadWriteOperations
     ************************************************************************/
    public function fieldsIncorrectReadWriteOperations() : void
    {
        $matchingRules                  = new MatchingRules(self::$correctParticipantsArray);
        $unusedParticipantField         = 'unusedParticipantFieldLongString';
        $alreadyUsedParticipant         = '';
        $alreadyUsedParticipantField    = '';

        foreach (self::$correctFieldsData as $systemField => $participantsFields)
            foreach ($participantsFields as $participant => $field)
            {
                $matchingRules->attachParticipantField($systemField, $participant, $field);
                if (strlen($alreadyUsedParticipant) <= 0)
                {
                    $alreadyUsedParticipant         = $participant;
                    $alreadyUsedParticipantField    = $field;
                }
            }

        foreach (self::$correctSystemFields as $systemField)
        {
            self::assertNull
            (
                $matchingRules->findParticipantField($systemField, self::$unusedParticipant),
                'Expect NULL on call "getParticipantField" with unseted participant'
            );
            self::assertNull
            (
                $matchingRules->findParticipantField('', self::$unusedParticipant),
                'Expect NULL on call "getParticipantField" with empty system field'
            );
            self::assertNull
            (
                $matchingRules->findParticipantField($systemField, ''),
                'Expect NULL on call "getParticipantField" with empty participant'
            );
        }

        foreach (self::$incorrectSystemFields as $systemField)
            foreach (self::$correctParticipantsArray as $participant)
                self::assertNull
                (
                    $matchingRules->findSystemField($systemField, $participant),
                    'Expect NULL on call "getParticipantField" with unseted system field'
                );

        foreach (self::$correctParticipantsArray as $participant)
        {
            self::assertNull
            (
                $matchingRules->findSystemField($participant, $unusedParticipantField),
                'Expect NULL on call "findSystemField" with unseted participant field'
            );
            self::assertNull
            (
                $matchingRules->findSystemField('', $unusedParticipantField),
                'Expect NULL on call "findSystemField" with empty participant'
            );
            self::assertNull
            (
                $matchingRules->findSystemField($participant, ''),
                'Expect NULL on call "findSystemField" with empty participant field'
            );
        }

        try
        {
            $matchingRules->attachParticipantField('systemField', 'participant', 'participantField');
            self::fail('Expect '.InvalidArgumentException::class.' on attach field with non class name participant');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }
        try
        {
            $matchingRules->attachParticipantField('systemField', self::$unusedParticipant, 'participantField');
            self::fail('Expect '.InvalidArgumentException::class.' on attach field with unseted participant');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }
        try
        {
            $matchingRules->attachParticipantField('', self::$correctParticipantsArray[0], 'someField');
            self::fail('Expect '.InvalidArgumentException::class.' on attach field empty system field');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }
        try
        {
            $matchingRules->attachParticipantField('someSystemField', self::$correctParticipantsArray[0], '');
            self::fail('Expect '.InvalidArgumentException::class.' on attach field empty participant field');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }

        foreach (self::$incorrectSystemFields as $unusedSystemField)
        {
            try
            {
                $matchingRules->attachParticipantField($unusedSystemField, $alreadyUsedParticipant, $alreadyUsedParticipantField);
                self::fail('Expect '.InvalidArgumentException::class.' on attach already used field with already used participant');
            }
            catch (InvalidArgumentException $error)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * testing clearing methods of matching same fields of different participants
     * @test
     * @depends fieldsReadWriteOperations
     ************************************************************************/
    public function fieldsClearingOperations() : void
    {
        $matchingRules = new MatchingRules(self::$correctParticipantsArray);

        foreach (self::$correctFieldsData as $systemField => $participantsFields)
            foreach ($participantsFields as $participant => $field)
                $matchingRules->attachParticipantField($systemField, $participant, $field);

        foreach (self::$correctFieldsData as $systemField => $participantsFields)
            foreach ($participantsFields as $participant => $field)
            {
                $matchingRules->detachParticipantField($systemField, $participant);
                self::assertNull
                (
                    $matchingRules->findParticipantField($systemField, $participant),
                    'Expect NULL after deleting participant field'
                );
            }
    }
    /** **********************************************************************
     * testing rules methods
     * @test
     * @depends fieldsReadWriteOperations
     ************************************************************************/
    public function rulesReadWriteOperations() : void
    {
        $matchingRules  = new MatchingRules(self::$correctParticipantsArray);
        $expectedRules  = [];
        $getedRules     = [];

        foreach (self::$correctSystemFields as $field)
        {
            $matchingRules->addMatchingRule(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1], [$field]);
            $expectedRules[] = [$field];
        }
        $matchingRules->addMatchingRule(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1], self::$correctSystemFields);
        $expectedRules[] = self::$correctSystemFields;

        $queue = $matchingRules->getMatchingRules(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1]);
        while (!$queue->isEmpty())
            $getedRules[] = $queue->pop();

        self::assertEquals
        (
            $expectedRules, $getedRules,
            'Geted marching rules not equals seted before'
        );
    }
    /** **********************************************************************
     * testing rules methods incorrect using
     * @test
     * @depends rulesReadWriteOperations
     ************************************************************************/
    public function rulesIncorrectReadWriteOperations() : void
    {
        $matchingRules = new MatchingRules(self::$correctParticipantsArray);

        foreach (self::$correctFieldsData as $systemField => $participantsFields)
            foreach ($participantsFields as $participant => $field)
                $matchingRules->attachParticipantField($systemField, $participant, $field);

        self::assertNull
        (
            $matchingRules->getMatchingRules('', self::$correctParticipantsArray[1]),
            'Expect NULL on call "getMatchingRules" with empty first participant'
        );
        self::assertNull
        (
            $matchingRules->getMatchingRules(self::$correctParticipantsArray[0], ''),
            'Expect NULL on call "getMatchingRules" with empty second participant'
        );

        try
        {
            $matchingRules->addMatchingRule(self::$correctParticipantsArray[0], self::$unusedParticipant, self::$correctSystemFields);
            self::fail('Expect '.InvalidArgumentException::class.' on adding matching rule with incorrect participant');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }
        try
        {
            $matchingRules->addMatchingRule(self::$unusedParticipant, self::$correctParticipantsArray[0], self::$correctSystemFields);
            self::fail('Expect '.InvalidArgumentException::class.' on adding matching rule with incorrect participant');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }

        foreach (self::$incorrectMatchingRules as $field)
        {
            try
            {
                $matchingRules->addMatchingRule(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1], $field);
                self::fail('Expect '.InvalidArgumentException::class.' on adding matching rule with incorrect fields array');
            }
            catch (InvalidArgumentException $error)
            {
                self::assertTrue(true);
            }
        }
    }
    /** **********************************************************************
     * testing rules methods clearing
     * @test
     * @depends rulesReadWriteOperations
     ************************************************************************/
    public function rulesClearingOperations() : void
    {
        $matchingRules = new MatchingRules(self::$correctParticipantsArray);

        foreach (self::$correctFieldsData as $systemField => $participantsFields)
            foreach ($participantsFields as $participant => $field)
                $matchingRules->attachParticipantField($systemField, $participant, $field);

        $matchingRules->addMatchingRule(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1], ['someField1', 'someField2']);
        $matchingRules->addMatchingRule(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1], ['someField3']);
        $matchingRules->clearMatchingRules(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1]);

        self::assertNull
        (
            $matchingRules->getMatchingRules(self::$correctParticipantsArray[0], self::$correctParticipantsArray[1]),
            'Expect NULL on call "getMatchingRules" with two participants after clearing'
        );
    }
}