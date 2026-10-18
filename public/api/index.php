<?php

ini_set('display_errors',1);

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

vehicle_lookup_rest_api::init();
vehicle_lookup_rest_api::validate_api();

vehicle_lookup_endpoint_registration::call_endpoint();

die();

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
var_dump($requestUri);
var_dump($method);
var_dump((new vehicle_lookup_database())::getConnection());
