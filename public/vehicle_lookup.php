<?php

// Interim Script to allow for secure secret
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$api_key = $_GET['api_key'] ?? '';
$api_url = $_ENV['API_URL'] ?? getenv('API_URL');
$secret = $_ENV['API_SECRET'] ?? getenv('API_SECRET');

$externalUrl = "$api_url/session_lookup?" . http_build_query([
    'vrm'            => $_GET['vrm'] ?? '',
    'fuzzy'          => true,
    'period_from'    => $_GET['period_from'] ?? '',
    'period_to'      => $_GET['period_to'] ?? '',
    'use_actual_end' => $_GET['use_actual_end'] ?? ''
]);


$auth_header = "Bearer $api_key:$secret";

$options = [
    "http" => [
        "method" => "GET",
        "header" => "Authorization: $auth_header\r\n" .
                    "Accept: application/json\r\n",
    ]
];

// Fetch API content
$context = stream_context_create($options);
$response = @file_get_contents($externalUrl, false, $context);


//Throw Error is no response
if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(['error' => 'API gateway connection failed']);
    exit;
}

echo $response;
