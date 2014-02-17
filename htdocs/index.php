<?php
define('APP_DIR', dirname(__FILE__) . '/../');

require APP_DIR . 'Config/lib.config.php';
require APP_DIR . 'Config/app.config.php';

AppController::createApp()->run();

?>