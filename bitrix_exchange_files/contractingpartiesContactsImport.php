<?php
declare(strict_types=1);
use Bitrix\Crm\EntityRequisite,
    Bitrix\Main\Loader,
    Bitrix\Crm\Binding\ContactCompanyTable,
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

$CCrmContact                = new CCrmContact;
$CCrmCompany                = new CCrmCompany;
$isNew                      = true;
$errors                     = [];
$PHONE_TYPES                = ['MOBILE', 'WORK', 'FAX', 'PAGER', 'HOME', 'OTHER'];
$company1cIdName            = 0;
$validateValue              = function ($arr, $key, $type = 'string'){
    switch ($type){
        case 'int':
            return array_key_exists($key, $arr)
                ? (int)$arr[$key]
                : 0;
            break;
        case 'date':
            return array_key_exists($key, $arr) &&
            preg_match('/([0-2]\d|3[01])\.(0\d|1[012])\.(\d{4})/', $arr[$key])
                ? $arr[$key]
                : '';
            break;
        case 'array':
            return array_key_exists($key, $arr) &&
            is_array($arr[$key])
                ? $arr[$key]
                :[];
            break;
        case 'stringToArray':
            return array_key_exists($key, $arr) &&
            is_array($arr[$key])
                ? $arr[$key]
                :[$arr[$key]];
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
/** ********************************************************************
 * Search UF COMPANY_1C_ID, COMPANY_CONTACT_1C_ID real field name
 *
 **********************************************************************/
foreach ($GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('CRM_COMPANY', 0, LANGUAGE_ID) as $field){
    if ($field['EDIT_FORM_LABEL'] == 'COMPANY_1C_ID'){
        $company1cIdName = strlen($field['FIELD_NAME']) > 0 ? $field['FIELD_NAME'] : '';
    }
}
foreach ($GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('CRM_CONTACT', 0, LANGUAGE_ID) as $field){
    if ($field['EDIT_FORM_LABEL'] == 'COMPANY_CONTACT_1C_ID') {
        $companyContact1cIdName = strlen($field['FIELD_NAME']) > 0 ? $field['FIELD_NAME'] : '';
    }
}
/** ********************************************************************
 * GENERAL CYCLE
 *
 **********************************************************************/
foreach ($data as $key => $item) {
    /** ********************************************************************
     * Search contact
     *
     **********************************************************************/
    $isNew              = true;
    $prefixNew          = 'n';
    $i                  = 0;
    $contactBitrixId    = $validateValue($item, 'ID', 'int');
    $companyContact1cId = $validateValue($item, 'COMPANY_CONTACT_1C_ID', 'string');
    $company1cIds        = $validateValue($item, 'COMPANY_1C_ID', 'stringToArray');
    $lastName           = $validateValue($item, 'LAST_NAME', 'string');
    $firstName          = $validateValue($item, 'NAME', 'string');
    $secondName         = $validateValue($item, 'SECOND_NAME', 'string');
    $birthday           = $validateValue($item, 'BIRTHDAY', 'date');
    $phone              = $validateValue($item, 'PHONE', 'array');
    $email              = $validateValue($item, 'EMAIL', 'array');
    $site               = $validateValue($item, 'SITE', 'array');
    $workPosition       = $validateValue($item, 'WORK_POSITION', 'string');
    $companyBitrixIds   = [];
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

    // find companies for COMPANY_1C_ID
    if (count($company1cIds) > 0 && strlen($company1cIdName) > 0){
        $arFilter = ['='.$company1cIdName => $company1cIds];
        $arSelect = ['ID', $company1cIdName];
        $rs = CCrmCompany::GetListEx([],$arFilter,false, false, $arSelect);
        while ($res=$rs->Fetch()){
            if(!array_key_exists($res[$company1cIdName], $companyBitrixIds) && strlen((string) $res[$company1cIdName]) > 0) {
                $companyBitrixIds[$res[$company1cIdName]] = $res['ID'];
            } /*else {
                $errors[] = 'Found more than one value companyBitrixId for company1cId - '.$res[$company1cIdName];
            }/**/
        }
    }
    //find user for COMPANY_CONTACT_1C_ID or ID
    if (strlen($companyContact1cId) > 0 || $contactBitrixId > 0 && strlen($companyContact1cIdName) > 0 ){
        $arFilter = [];
        $arSelect = ['ID', $companyContact1cIdName];
        $rs = CCrmContact::GetListEx([],$arFilter,false, false, $arSelect);
        while ($res=$rs->Fetch()){
            if (strlen((string) $res[$companyContact1cIdName]) > 0 && $res[$companyContact1cIdName] == $companyContact1cId){
                $contactBitrixId = (int) $res['ID'];
                $isNew = false;
                break;
            } else if (strlen((string) $res['ID']) > 0 && $res['ID'] == $companyContact1cId){
                $isNew = false;
            }
        }
    }
    // add multiFields if item not new
    if ($isNew === false) {
        $arFiler = ["ELEMENT_ID" => $contactBitrixId];
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
    $arFieldsContact = [
        'LAST_NAME'     => $lastName,
        'NAME'          => $firstName,
        'SECOND_NAME'   => $secondName,
        'BIRTHDATE'     => $birthday,
        'POST'          => $workPosition,
        'FM' => [
            'PHONE'     => $phones,
            'EMAIL'     => $emails,
            'WEB'       => $sites
        ],
    ];
    //add UF COMPANY_1C_ID
    if (strlen($companyContact1cIdName) > 0 && strlen($companyContact1cId) > 0) {
        $arFieldsContact[$companyContact1cIdName] = $companyContact1cId;
    }
    if ($isNew === true){
        /** ********************************************************************
         * SAVE COMPANY FIELDS
         *
         **********************************************************************/
        if ((bool) $newId = $CCrmContact->Add($arFieldsContact, true)){
            if (count($companyBitrixIds) > 0) {
                ContactCompanyTable::bindCompanyIDs($newId, $companyBitrixIds);
            }
        }
    } else {
        /** ********************************************************************
         * UPDATE RESULTS
         *
         **********************************************************************/

        if ((bool) $CCrmContact->Update($contactBitrixId, $arFieldsContact)) {
            if (count($companyBitrixIds) > 0) {
                ContactCompanyTable::unbindAllCompanies($contactBitrixId);
                ContactCompanyTable::bindCompanyIDs($contactBitrixId, $companyBitrixIds);
            }
        }
    }
    if (strlen($CCrmCompany->LAST_ERROR) > 0){
        $errors[] = $CCrmCompany->LAST_ERROR;
    }
}