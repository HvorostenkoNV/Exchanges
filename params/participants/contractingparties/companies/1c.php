<?php
declare(strict_types=1);
/** ***********************************************************************************************
 * Application main params
 *
 * @package exchange_params
 * @author  Hvorostenko
 *************************************************************************************************/
return
    [
        'implodedReceivedDataPath'  => 'contractingparties'.DIRECTORY_SEPARATOR.'implodedReceived',
        'implodedReturnedDataPath'  => 'contractingparties'.DIRECTORY_SEPARATOR.'implodedReturned',
        'implodedProcessedDataPath' => 'contractingparties'.DIRECTORY_SEPARATOR.'implodedProcessed',
        'receivedDataPath'          => 'contractingparties'.DIRECTORY_SEPARATOR.'companies'.DIRECTORY_SEPARATOR.'1c'.DIRECTORY_SEPARATOR.'received',
        'returnedDataPath'          => 'contractingparties'.DIRECTORY_SEPARATOR.'companies'.DIRECTORY_SEPARATOR.'1c'.DIRECTORY_SEPARATOR.'returned'
    ];