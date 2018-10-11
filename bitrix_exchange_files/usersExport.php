<?php
declare(strict_types=1);
/** **********************************************************************
 * @var array $data
 ************************************************************************/
$dateYesterday  = new DateTime;
$dateTomorrow   = new DateTime;

$dateYesterday->modify('-1 day');
$dateTomorrow->modify('+1 day');

$filter =
    [
        'TIMESTAMP_1'   => $dateYesterday->format('d.m.Y'),
        'TIMESTAMP_2'   => $dateTomorrow->format('d.m.Y')
    ];
$select =
    [
        'SELECT' => ['UF_1C_USER_ID']
    ];

$queryList = CUser::GetList($by = 'ID', $sort = 'ASC', $filter, $select);
while ($queryItem = $queryList->GetNext())
{
    $data[] =
        [
            'ID'            => $queryItem['ID'],
            'NAME'          => $queryItem['NAME'],
            'LAST_NAME'     => $queryItem['LAST_NAME'],
            'SECOND_NAME'   => $queryItem['SECOND_NAME'],
            'GENDER'        => $queryItem['PERSONAL_GENDER'],
            'BIRTHDAY'      => $queryItem['PERSONAL_BIRTHDAY'],
            'WORK_POSITION' => $queryItem['WORK_POSITION'],
            'MOBILE'        => $queryItem['WORK_PHONE'],
            'ACTIVE'        => $queryItem['ACTIVE'],
            'USER_1C_ID'    => $queryItem['UF_1C_USER_ID']
        ];
}