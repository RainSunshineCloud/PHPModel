<?php

use Yaf\Application;
use Yaf\Exception;

define('APPLICATION_PATH', dirname(dirname(__FILE__)));

require '../vendor/autoload.php';

$application = new Application( APPLICATION_PATH . "/conf/application.ini");
$application->getDispatcher()->disableView();
$application->bootstrap()->run();

?>
