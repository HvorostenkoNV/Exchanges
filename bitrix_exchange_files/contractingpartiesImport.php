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
 * @var array   $data
 * @var array   $errors
 ************************************************************************/
$CCrmCompany                = new CCrmCompany;
$isNew                      = true;
$company1cIdName            = '';
$res                        = '';
$userManager1cIds           = [];
$userBitrixIds              = [];
$COMPANY_TYPE               = [];
$INDUSTRY                   = [];
$PHONE_TYPES                = ['MOBILE', 'WORK', 'FAX', 'PAGER', 'HOME', 'OTHER'];
$validateValue              = function ($arr, $key, $type = 'string'){
    switch ($type){
        case 'int':
            return array_key_exists($key, $arr)
                ? (int)$arr[$key]
                : 0;
            break;
        case 'array':
            return array_key_exists($key, $arr) &&
            is_array($arr[$key])
                ? $arr[$key]
                : [];
            break;
        case 'string':
        default:
            return (array_key_exists($key, $arr) &&
                is_string($arr[$key]) &&
                strlen($arr[$key]) > 0)
                ? (string) htmlspecialchars($arr[$key])
                : '';
            break;
    }
};

/** **********************************************************************
 * Get CRM Status Fields Types
 ************************************************************************/
$res = CCrmStatus::GetList(['SORT' => 'ASC']);
while($status = $res->Fetch()){
    switch ($status['ENTITY_ID']){
        case 'COMPANY_TYPE':
            $COMPANY_TYPE[] = $status['STATUS_ID'];
            break;
        case 'INDUSTRY':
            $INDUSTRY[] = $status['STATUS_ID'];
            break;
    }
}
/** ********************************************************************
 * Search UF COMPANY_1C_ID real field name
 *
 **********************************************************************/
foreach ($GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('CRM_COMPANY', 0, LANGUAGE_ID) as $field){
    if ($field['EDIT_FORM_LABEL'] == 'COMPANY_1C_ID'){
        $company1cIdName = strlen($field['FIELD_NAME']) > 0 ? $field['FIELD_NAME'] : '';
    }
}
/** ********************************************************************
 * Search UF_MANAGER_1C_IDs
 *
 **********************************************************************/
foreach ($data as $key => $item) {
    if (strlen((string)$item['MANAGER_1C_ID']) > 0) {
        $userManager1cIds[] = $item['MANAGER_1C_ID'];
    }
}
if (count($userManager1cIds) > 0) {
    $arFiler = ['UF_1C_USER_ID' => $userManager1cIds];
    $arSelect = ['SELECT' => ['UF_1C_USER_ID']];
    $queryList = CUser::GetList($by = 'ID', $sort = 'ASC', $arFiler, $arSelect);
    while ($queryItem = $queryList->Fetch()) {
        if (!array_key_exists($queryItem['UF_1C_USER_ID'], $userBitrixIds)) {
            $userBitrixIds[$queryItem['UF_1C_USER_ID']] = (int)$queryItem['ID'];
        }
    }
}
/** ********************************************************************
 * GENERAL CYCLE
 *
 **********************************************************************/
foreach ($data as $key => $item) {
    /** ********************************************************************
     * Search company
     *
     **********************************************************************/
    $isNew              = true;
    $prefixNew          = 'n';
    $i                  = 0;
    $companyBitrixId    = $validateValue($item, 'ID', 'int');
    $company1cId        = $validateValue($item, 'COMPANY_1C_ID', 'string');
    $comment            = $validateValue($item, 'COMMENTS', 'string');
    $companyINN         = $validateValue($item, 'INN', 'string');
    $companyOKPO        = $validateValue($item, 'OKPO', 'string');
    $companyName        = $validateValue($item, 'NAME', 'string');
    $companyType        = $validateValue($item, 'TYPE', 'string');
    $industry           = $validateValue($item, 'INDUSTRY', 'string');
    $userManager1cId    = $validateValue($item, 'MANAGER_1C_ID', 'string');
    $phone              = $validateValue($item, 'PHONE', 'array');
    $email              = $validateValue($item, 'EMAIL', 'array');
    $site               = $validateValue($item, 'SITE', 'array');
    $phones             = [];
    $emails             = [];
    $sites              = [];
    $multiFieldValues   = [
        'PHONE' => [],
        'WEB'   => [],
        'EMAIL' => []
    ];

    //validate $phone
    if (count($phone) > 0){
        foreach ($phone as $type => $numbers){
            if (is_array($numbers) && count($numbers) > 0){
                foreach ($numbers as $number) {
                    $phones['n'.$i++] = [
                        'VALUE_TYPE' => in_array($type, $PHONE_TYPES) ? $type : 'OTHER',
                        'VALUE' => $number
                    ];
                }
            }
        }
    }
    //validate $email
    if (count($email) > 0){
        foreach ($email as $valueEmail){
            $emails[$prefixNew.$i++] = [
                'VALUE_TYPE' => 'OTHER',
                'VALUE' => $valueEmail
            ];
        }
    }
    //validate $site
    if (count($site) > 0){
        foreach ($site as $valueSite){
            $sites[$prefixNew.$i++] = [
                'VALUE_TYPE' => 'OTHER',
                'VALUE' => $valueSite
            ];
        }
    }
    // find company for COMPANY_1C_ID or ID
    if (strlen($company1cId) > 0 ||  $companyBitrixId > 0){
        $arFilter = [];
        $arSelect = ['ID', $company1cIdName];
        $rs = CCrmCompany::GetListEx([],$arFilter,false, false, $arSelect);
        while ($res=$rs->Fetch()){
            if (strlen($company1cIdName) > 0 && strlen((string) $res[$company1cIdName]) > 0 && $res[$company1cIdName] == $company1cId){
                $companyBitrixId = (int) $res['ID'];
                $isNew = false;
                break;
            } else if (strlen((string) $res['ID']) > 0 && $res['ID'] == $companyBitrixId){
                $isNew = false;
            }
        }

    }
    // find company for INN and OKPO
    if ($isNew === true && (strlen($companyINN) > 0 || strlen($companyOKPO) > 0)){
        $arFilter = [
            'ENTITY_TYPE_ID' => CCrmOwnerType::Company, //type of Entity in CRM 4 for Company
            [
                'LOGIC' => 'OR',
                [
                    'RQ_INN'  => $companyINN,
                    '!RQ_INN' => ''
                ],
                [
                    'RQ_OKPO'  => $companyOKPO,
                    '!RQ_OKPO' => ''
                ]
            ],
        ];
        $arSelect = ['ENTITY_ID'];
        $req = new EntityRequisite;
        $rs = $req->getList(["filter" => $arFilter ,'select' => $arSelect]);
        while($res=$rs->Fetch()){
            if (strlen($res['ENTITY_ID']) > 0){
                $companyBitrixId = (int) $res['ENTITY_ID'];
                $isNew = false;
                break;
            }
        }
    }
    // add multiFields if item not new
    if ($isNew === false) {
        $arFiler = ["ELEMENT_ID" => $companyBitrixId];
        $rs = CCrmFieldMulti::GetList([], $arFiler);
        while ($res = $rs->Fetch()) {
            switch ($res['TYPE_ID']) {
                case 'PHONE':
                    $multiFieldValues['PHONE'][$res['ID']] = [];
                    break;
                case 'WEB':
                    $multiFieldValues['WEB'][$res['ID']] = [];
                    break;
                case 'EMAIL':
                    $multiFieldValues['EMAIL'][$res['ID']] = [];
                    break;
            }
        }
        $phones = count($phones) > 0 ? $phones + $multiFieldValues['PHONE'] : $phones;
        $emails = count($emails) > 0 ? $emails + $multiFieldValues['EMAIL'] : $emails;
        $sites  = count($sites)  > 0 ? $sites  + $multiFieldValues['WEB']   : $sites;

    }
    /** ********************************************************************
     * SAVE RESULTS
     *
     **********************************************************************/
    $arFieldsComp = [
        'TITLE'             => $companyName,
        'COMPANY_TYPE'      => in_array($companyType, $COMPANY_TYPE) ? $companyType : 'OTHER',
        'INDUSTRY'          => in_array($industry, $INDUSTRY) ? $industry : 'OTHER',
        'COMMENTS'          => $comment,
        'ASSIGNED_BY_ID'    => (array_key_exists($userManager1cId ,$userBitrixIds) &&
            strlen((string)$userBitrixIds[$userManager1cId]) > 0) ?
            $userBitrixIds[$userManager1cId] : 0,
        'FM' => [
            'PHONE' => $phones,
            'EMAIL' => $emails,
            'WEB'   => $sites
        ],
    ];
    //add UF COMPANY_1C_ID
    if (strlen($company1cIdName) > 0 && array_key_exists('COMPANY_1C_ID', $item)) {
        $arFieldsComp[$company1cIdName] = $item['COMPANY_1C_ID'];
    }

    if ($isNew === true){
        /** ********************************************************************
         * SAVE COMPANY FIELDS
         *
         **********************************************************************/
        $CCrmCompany->Add($arFieldsComp, true);
    } else {
        /** ********************************************************************
         * UPDATE RESULTS
         *
         **********************************************************************/
        $CCrmCompany->Update($companyBitrixId, $arFieldsComp);
    }
    if (strlen($CCrmCompany->LAST_ERROR) > 0){
        $errors[] = $CCrmCompany->LAST_ERROR;
    }
}
