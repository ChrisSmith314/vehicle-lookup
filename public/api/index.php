<?php

ini_set('display_errors',1);

require_once __DIR__ . '/../vendor/autoload.php';

foreach(glob('includes/*.php') as $class){
    include $class;
}

var_dump((new vehicle_lookup_database())::getConnection());
