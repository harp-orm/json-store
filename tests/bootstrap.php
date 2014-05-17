<?php

error_reporting(E_ALL);

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('CL\\LunaJsonStore\\Test\\', __DIR__.'/src');

define('TEST_DIR', __DIR__.DIRECTORY_SEPARATOR.'repos');
