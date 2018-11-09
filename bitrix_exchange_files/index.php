<?php
declare(strict_types=1);
/** **********************************************************************
 * @var CUser $USER
 ************************************************************************/
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
header('Content-Type:application/json;charset=utf-8');
/** **********************************************************************
 * variables
 ************************************************************************/
$exchangeGroupCode  = 'exchange';
$operationType      = (string)  ($_REQUEST['type']      ?? '');
$getedLogin         = (string)  ($_REQUEST['login']     ?? '');
$getedPassword      = (string)  ($_REQUEST['password']  ?? '');
$getedData          = (array)   ($_REQUEST['data']      ?? []);
$answer             =
    [
        'result'    => '',
        'errors'    => [],
        'data'      => []
    ];
$throwAnswer        = function(array $answerData)
{
    exit(json_encode($answerData));
};
/** **********************************************************************
 * authentication
 ************************************************************************/
if (!$USER->IsAuthorized())
{
    $loginSuccess = $USER->Login($getedLogin, $getedPassword);
    if ($loginSuccess !== true)
    {
        $answer['result']   = 'error';
        $answer['errors'][] = 'authentication failed';
        $throwAnswer($answer);
    }
}
/** **********************************************************************
 * authorization
 ************************************************************************/
$exchangeGroupId    = 0;
$userGroups         = CUser::GetUserGroupArray();

$queryList = CGroup::GetList($by = 'ID', $sort = 'ASC', ['STRING_ID' => $exchangeGroupCode]);
while ($queryItem = $queryList->GetNext())
{
    $exchangeGroupId = (int) $queryItem['ID'];
}

if (!$USER->IsAdmin() && !in_array($exchangeGroupId, $userGroups))
{
    $answer['result']   = 'error';
    $answer['errors'][] = 'authorization failed';
    $throwAnswer($answer);
}
/** **********************************************************************
 * operation type calc
 ************************************************************************/
$operationFileName  = '';
$isExport           = false;
$isImport           = false;

switch ($operationType)
{
    case 'usersExport':
        $operationFileName  = 'usersExport';
        $isExport           = true;
        break;
    case 'usersImport':
        $operationFileName  = 'usersImport';
        $isImport           = true;
        break;
    case 'contractingpartiesExport':
        $operationFileName  = 'contractingpartiesExport';
        $isExport           = true;
        break;
    case 'contractingpartiesImport':
        $operationFileName  = 'contractingpartiesImport';
        $isImport           = true;
        break;
    case 'contractingpartiesContactsExport':
        $operationFileName  = 'contractingpartiesContactsExport';
        $isExport           = true;
        break;
    case 'contractingpartiesContactsImport':
        $operationFileName  = 'contractingpartiesContactsImport';
        $isImport           = true;
        break;
    case 'contractingpartiesRequisitesExport':
        $operationFileName  = 'contractingpartiesRequisitesExport';
        $isExport           = true;
        break;
    case 'contractingpartiesRequisitesImport':
        $operationFileName  = 'contractingpartiesRequisitesImport';
        $isExport           = true;
        break;
    case 'contractingpartiesAddressesExport':
        $operationFileName  = 'contractingpartiesAddressesExport';
        $isExport           = true;
        break;
    case 'contractingpartiesAddressesImport':
        $operationFileName  = 'contractingpartiesAddressesImport';
        $isExport           = true;
        break;
    default:
}
/** **********************************************************************
 * output
 ************************************************************************/
if (strlen($operationFileName) <= 0 || !($isExport || $isImport))
{
    $answer['result']   = 'error';
    $answer['errors'][] = 'unknown operation type';
    $throwAnswer($answer);
}

try
{
    if ($isExport)
    {
        $data   = [];
        $errors = [];

        include "$operationFileName.php";

        $answer['result']   = 'ok';
        $answer['errors']   = (array) $errors;
        $answer['data']     = (array) $data;
    }
    elseif ($isImport)
    {
        $data   = $getedData;
        $errors = [];

        include "$operationFileName.php";

        $answer['result']   = 'ok';
        $answer['errors']   = (array) $errors;
    }

    $throwAnswer($answer);
}
catch (Throwable $exception)
{
    $answer['result']   = 'error';
    $answer['errors'][] = $exception->getMessage();

    $throwAnswer($answer);
}