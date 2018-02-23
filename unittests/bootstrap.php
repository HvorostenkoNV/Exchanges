<?php
$dirPathExplode = explode(DIRECTORY_SEPARATOR, __DIR__);
unset($dirPathExplode[count($dirPathExplode) - 1]);

define('DOCUMENT_ROOT_BY_UT', implode(DIRECTORY_SEPARATOR, $dirPathExplode));

require 'ExchangeTestCase.php';
require DOCUMENT_ROOT_BY_UT.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';