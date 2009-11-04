<?php

date_default_timezone_set('Europe/Samara');
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
set_include_path(
    APPLICATION_PATH . '/lib' . PATH_SEPARATOR . get_include_path()
);
set_include_path(
    APPLICATION_PATH . '/tests' . PATH_SEPARATOR . get_include_path()
);
