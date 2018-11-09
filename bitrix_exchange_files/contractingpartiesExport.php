<?php
declare(strict_types=1);

use Bitrix\Crm\EntityRequisite,
    Bitrix\Main\Loader,
    Bitrix\Main\LoaderException;

try {
    Loader::includeModule('crm');
} catch (LoaderException $exception) {
    throw $exception;
}
/** **********************************************************************
 * @var array $data
 ************************************************************************/
/*
 * data = [
 *     x 'ID'                => ID           // CCrmCompany::GetListEx,
 *     x 'COMPANY_1C_ID'     =>              // CCrmCompany::GetListEx, name of field search in $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('CRM_COMPANY', 0, LANGUAGE_ID)
 *     x 'ACTIVE'            => true         // true
 *     x 'NAME'              => TITLE        // CCrmCompany::GetListEx
 *     x 'TYPE'              => COMPANY_TYPE // CCrmCompany::GetListEx
 *     x 'SCOPE'             => INDUSTRY     // CCrmCompany::GetListEx
 *     x 'MANAGER_1C_ID'     =>              // CUser::GetList
 *     x 'PHONE'             =>              // CCrmFieldMulti::GetListEx 'ENTITY_ID' => 'COMPANY' 'ELEMENT_ID' => $COMPANY_ID
 *     x 'EMAIL'             =>              // CCrmFieldMulti::GetListEx 'ENTITY_ID' => 'COMPANY' 'ELEMENT_ID' => $COMPANY_ID
 *     x 'SITE'              =>              // CCrmFieldMulti::GetListEx 'ENTITY_ID' => 'COMPANY' 'ELEMENT_ID' => $COMPANY_ID
 *     x 'COMMENTS'          => COMMENTS	 // CCrmCompany::GetListEx
 *       'INN'               =>              // new EntityRequisite->GetList
 *       'OKPO'              =>              // new EntityRequisite->GetList
 *]
 * */

/** *************************************************************************************
 **************** VARs******************************************************************
 ***************************************************************************************/
$data                   = [];
$company1cIdName        = '';
$companyIds             = [];
$managersId             = [];
$resultBaseFieldsArray  = [];
$managersUf1cUserId     = [];
$resultMultiFieldsArray = [];
$requisiteResult        = [];
$requisiteResultNew     = [];

/** ************************************************************************************
 **************** 1.GET COMPANY_1C_ID NAME**************************************************
 ***************************************************************************************/

foreach ($GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('CRM_COMPANY', 0, LANGUAGE_ID) as $field){
    if ($field['EDIT_FORM_LABEL'] == 'COMPANY_1C_ID'){
        $company1cIdName =  $field['FIELD_NAME'];
    }
}
/** **************************************************************************************
 *** 2.GET BASE FIELDS FROM COMPANY + SAVE ALL COMPANY IDs + SAVE COMPANY MANAGERS IDs***
 ****************************************************************************************/
$arFilter = ['>=DATE_MODIFY' => (new DateTime)->modify('-1 day')->format('d.m.Y')];
$arSelect = ['ID', 'COMPANY_TYPE', 'TITLE', 'INDUSTRY', 'COMMENTS', 'ASSIGNED_BY', 'DATE_MODIFY', $company1cIdName];

$rs = CCrmCompany::GetListEx([],$arFilter,false, false, $arSelect);
while ($res=$rs->Fetch()){
    $resultBaseFieldsArray[$res['ID']] = $res; //$res['ID'] - id company
    $managersId[] = $res['ASSIGNED_BY'];
    $companyIds[] = $res['ID'];
}
$managersId = array_unique($managersId);
$managersId = array_values($managersId);
/** *************************************************************************************
 **************** 3.GET UF_1C_USER_ID FOR COMPANIES_1C_ID NAME**************************
 ***************************************************************************************/

$arFilter = ['ID' => implode('|', $managersId)];
$arSelect = ['FIELDS' => ['ID', 'NAME', 'LAST_NAME'], 'SELECT' => ['UF_1C_USER_ID']];

$rs = CUser::GetList($by='id', $order = "desc", $arFilter, $arSelect);
while ($res=$rs->Fetch()) {
    $managersUf1cUserId[$res['ID']] = $res['UF_1C_USER_ID'];
}
/** *************************************************************************************
 **************** 4.GET MULTI FIELDS FROM COMPANIES ************************************
 ***************************************************************************************/

$arFilter = ['ENTITY_ID' => CCrmOwnerType::CompanyName, 'ELEMENT_ID' => $companyIds];
$arSelect = [];

$rs = CCrmFieldMulti::GetListEx([], $arFilter, false, false, $arSelect, []);

while ($res=$rs->Fetch())
{
    $companyId  = $res['ELEMENT_ID'];
    $fieldType  = $res['TYPE_ID'];
    $valueType  = $res['VALUE_TYPE'];

    if (!array_key_exists($companyId, $resultMultiFieldsArray)){
        $resultMultiFieldsArray[$companyId] = [];
    }
    if (!array_key_exists($fieldType, $resultMultiFieldsArray[$companyId])){
        $resultMultiFieldsArray[$companyId][$fieldType] = [];
    }
    if (!array_key_exists($valueType, $resultMultiFieldsArray[$companyId][$fieldType])){
        $resultMultiFieldsArray[$companyId][$fieldType][$valueType] = [];
    }

    $resultMultiFieldsArray[$companyId][$fieldType][$valueType][] = $res['VALUE'];
}
/** *************************************************************************************
 **************** 5.GET INN AND OKPO ***************************************************
 ***************************************************************************************/

$arFilter = [
    "ENTITY_ID" => $companyIds,  //id Company
    "ENTITY_TYPE_ID" => CCrmOwnerType::Company, //type of Entity in CRM 4 for Company
];
$req = new EntityRequisite;
$rs = $req->getList(["filter" => $arFilter]);

while($row = $rs->fetch()){
    $requisiteResult[$row['ENTITY_ID']][] = $row;
}

foreach ($requisiteResult as $key => $requisite){
    foreach ($requisite as $r) {
        if (!array_key_exists($key, $requisiteResultNew)){
            $requisiteResultNew[$key] = [];
        }

        $requisiteResultNew[$key]['INN']  = empty($r['RQ_INN'])  ? $requisiteResultNew[$key]['INN']  : $r['RQ_INN'];
        $requisiteResultNew[$key]['OKPO'] = empty($r['RQ_OKPO']) ? $requisiteResultNew[$key]['OKPO'] : $r['RQ_OKPO'];
    }
}
/** *************************************************************************************
 **************** 6.SAVE RESULT TO $data ***********************************************
 ***************************************************************************************/
foreach ($resultBaseFieldsArray as $key => $value){

    $id         = array_key_exists('ID', $value)            ? $value['ID']              : null;
    $name       = array_key_exists('TITLE', $value)         ? $value['TITLE']           : null;
    $type       = array_key_exists('COMPANY_TYPE', $value)  ? $value['COMPANY_TYPE']    : null;
    $scope      = array_key_exists('INDUSTRY', $value)      ? $value['INDUSTRY']        : null;
    $comm       = array_key_exists('COMMENTS', $value)      ? $value['COMMENTS']        : null;
    $id1c       = !empty($company1cIdName) &&
    array_key_exists($company1cIdName, $value)
        ? $value[$company1cIdName]
        : null;
    $manager    = array_key_exists('ASSIGNED_BY', $value) &&
    array_key_exists($value['ASSIGNED_BY'], $managersUf1cUserId)
        ? $managersUf1cUserId[$value['ASSIGNED_BY']]
        : null;
    $phone      = array_key_exists($key, $resultMultiFieldsArray) &&
    array_key_exists('PHONE', $resultMultiFieldsArray[$key])
        ? $resultMultiFieldsArray[$key]['PHONE']
        : [];
    $email      = array_key_exists($key, $resultMultiFieldsArray) &&
    array_key_exists('EMAIL', $resultMultiFieldsArray[$key])
        ? $resultMultiFieldsArray[$key]['EMAIL']
        : [];
    $site       = array_key_exists($key, $resultMultiFieldsArray) &&
    array_key_exists('WEB', $resultMultiFieldsArray[$key])
        ? $resultMultiFieldsArray[$key]['WEB']
        : [];
    $inn        = array_key_exists($key, $requisiteResultNew) &&
    array_key_exists('INN', $requisiteResultNew[$key])
        ? $requisiteResultNew[$key]['INN']
        : null;
    $okpo       = array_key_exists($key, $requisiteResultNew) &&
    array_key_exists('OKPO', $requisiteResultNew[$key])
        ? $requisiteResultNew[$key]['OKPO']
        : null;
    $data[] =
        [
            'ID'                => $id,
            'COMPANY_1C_ID'     => $id1c,
            'ACTIVE'            => true,
            'NAME'              => $name,
            'TYPE'              => $type,
            'SCOPE'             => $scope,
            'MANAGER_1C_ID'     => $manager,
            'PHONE'             => $phone,
            'EMAIL'             => $email,
            'SITE'              => $site,
            'COMMENTS'          => $comm,
            'INN'               => $inn,
            'OKPO'              => $okpo,
        ];

}
/**/




