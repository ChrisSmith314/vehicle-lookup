<?php

// 1. The public key identifier (safe to store in plain text)
$apiKey = 'vrm_live_key_' . bin2hex(random_bytes(6));

// 2. The secret token (given to the client once, and hashed for the DB)
$rawSecret = bin2hex(random_bytes(16));
$hashedSecret = password_hash($rawSecret, PASSWORD_DEFAULT);

echo "Give this to the client (Header: Bearer {$apiKey}:{$rawSecret})<br>";
echo "Save this hash in the database column 'api_secret': {$hashedSecret}";

echo '<br><br>';

echo "<code>INSERT INTO api_keys (client_name, api_key, api_secret) VALUES ('Example User', '{$apiKey}', '{$hashedSecret}')</code>";
