<?php
declare(strict_types=1);

use Main\Application;
/** ***********************************************************************************************
 * exchange entrance file
 *
 * @package exchange
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

$testTestTest = 1;
while ($testTestTest > 0)
{
    Application::getInstance()->getExchange()->run();
    $testTestTest--;
}