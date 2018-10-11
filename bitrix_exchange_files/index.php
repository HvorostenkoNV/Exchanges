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
$operationType      = array_key_exists('type', $_REQUEST)       ? $_REQUEST['type']         : '';
$getedLogin         = array_key_exists('login', $_REQUEST)      ? $_REQUEST['login']        : '';
$getedPassword      = array_key_exists('password', $_REQUEST)   ? $_REQUEST['password']     : '';
$getedData          = array_key_exists('data', $_REQUEST)       ? (array) $_REQUEST['data'] : [];
$answer             =
    [
        'result'    => '',
        'message'   => '',
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
        $answer['message']  = 'Authentication failed';
        $throwAnswer($answer);
    }
}
/** **********************************************************************
 * authorization
 ************************************************************************/
$userGroups = CUser::GetUserGroupArray();
$queryList  = CGroup::GetList
(
    $by     = 'ID',
    $sort   = 'ASC',
    [
        'ID'        => $userGroups,
        'STRING_ID' => $exchangeGroupCode
    ]
);

if (!$USER->IsAdmin() && $queryList->SelectedRowsCount() <= 0)
{
    $answer['result']   = 'error';
    $answer['message']  = 'Authorization failed';
    $throwAnswer($answer);
}
/** **********************************************************************
 * operation run
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
    default:
}

if (strlen($operationFileName) <= 0 || !($isExport || $isImport))
{
    $answer['result']   = 'error';
    $answer['message']  = 'Unknown operation type';
    $throwAnswer($answer);
}

try
{
    if ($isExport)
    {
        $data = [];

        include "$operationFileName.php";

        $answer['result']   = 'ok';
        $answer['data']     = is_array($data) ? $data : [];
    }
    elseif ($isImport)
    {
        $data   = $getedData;
        $errors = [];

        include "$operationFileName.php";

        $answer['result']   = count($errors) > 0 ? 'error' : 'ok';
        $answer['message']  = implode(', ', $errors);
    }

    $throwAnswer($answer);
}
catch (Throwable $exception)
{
    $answer['result']   = 'error';
    $answer['message']  = $exception->getMessage();
}