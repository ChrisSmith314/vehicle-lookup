<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once __DIR__ . '/../../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

foreach(glob('includes/*.php') as $class){
    include $class;
}

$api_route = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

vehicle_lookup_rest_api::init();
vehicle_lookup_rest_api::validate_api();

vehicle_lookup_endpoint_registration::call_endpoint();
