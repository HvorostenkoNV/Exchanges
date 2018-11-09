<?php
declare(strict_types=1);
/** **********************************************************************
 * @var array   $data
 * @var array   $errors
 ************************************************************************/
$userFrom1cBaseLogin    = 'user.from.1c';
$fieldsMatching         =
    [
        'NAME'              => 'NAME',
        'LAST_NAME'         => 'LAST_NAME',
        'SECOND_NAME'       => 'SECOND_NAME',
        'PERSONAL_GENDER'   => 'GENDER',
        'PERSONAL_BIRTHDAY' => 'BIRTHDAY',
        'WORK_POSITION'     => 'WORK_POSITION',
        'WORK_PHONE'        => 'MOBILE',
        'ACTIVE'            => 'ACTIVE',
        'UF_1C_USER_ID'     => 'USER_1C_ID'
    ];
$generatePassword       = function()
{
    $chars  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count  = mb_strlen($chars);
    $result = '';

    for ($index = 10; $index > 0; $index--)
    {
        $result .= mb_substr($chars, rand(0, $count - 1), 1);
    }

    return $result;
};

foreach ($data as $item)
{
    if (!is_array($item))
    {
        continue;
    }

    $user           = new CUser;
    $userData       = [];
    $userBitrixId   = (int)     $item['ID'];
    $user1cId       = (string)  $item['USER_1C_ID'];

    foreach ($fieldsMatching as $bitrixField => $exchangeField)
    {
        $userData[$bitrixField] = $item[$exchangeField] ?? null;
    }
    $userData['TIMESTAMP_X'] = (new DateTime)->format('d.m.Y H:i:s');

    if ($userBitrixId <= 0 && strlen($user1cId) > 0)
    {
        $queryList = CUser::GetList($by = 'ID', $sort = 'ASC', ['UF_1C_USER_ID' => $user1cId]);
        while ($queryItem = $queryList->GetNext())
        {
            $userBitrixId = (int) $queryItem['ID'];
        }
    }

    if ($userBitrixId <= 0)
    {
        $userData['LOGIN']      = $userFrom1cBaseLogin.'.'.$user1cId;
        $userData['EMAIL']      = "$userFrom1cBaseLogin.$user1cId@test.ua";
        $userData['PASSWORD']   = $generatePassword();

        $user->Add($userData);
    }
    else
    {
        $user->Update($userBitrixId, $userData);
    }

    if (strlen($user->LAST_ERROR) > 0)
    {
        $errors[] = $user->LAST_ERROR;
    }
}