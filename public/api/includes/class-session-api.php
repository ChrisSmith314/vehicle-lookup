<?php

class vehicle_lookup_rest_api {
    private static $database;
    private static $instance;
    
    public static function init() {
        self::$database = new vehicle_lookup_database();
        self::$database->getConnection();
        
        self::register_endpoints();
    }
    
    private static function register_endpoints(){
        //List API Endpoints in array and then pass to register_endpoint function
        $endpoints = [
            'register_session' => [self::getInstance(), 'register_session'],
            'end_session' => [self::getInstance(), 'end_session'],
            'get_session' => [self::getInstance(), 'get_session'],
            'session_lookup' => [self::getInstance(), 'session_lookup'],
        ];
        
        foreach($endpoints as $endpoint => $function){
            register_endpoint($endpoint, $function);
        }
    }
    
    public static function getInstance(){
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Validate API session
    public static function validate_api(){
        //Temp Disable 
        return true;
        
        // Validate API key against recorded user
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        // Get Key
        $auth = '';
        $apiKey = '';
        $apiSecret = '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $auth = $matches[1];
            [$apiKey, $apiSecret] = explode(":", $auth, 2);
        }
        
        // If no key provide, return error
        if (!$apiKey || !$apiSecret) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Missing or malformed API key.']);
            exit;
        }
        
        // Fetch API User from database
        $query = "SELECT * FROM api_keys WHERE api_key = ?";
        
        $account = self::$database->query($query, [$apiKey]);
        
        //Verify Credentials
        if($account && isset($account['api_secret'])){
            if(password_verify($apiSecret, $account['api_secret'])){
               return true; 
            }
        }
        
        
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unable to verify account.']);
        exit;
    }
    
    // Register Session Endpoint
    public function register_session(){
        echo "Registering a session";
    }
    
    // End Session Endpoint
    public function end_session(){
        echo "Registering the end of a session";
    }
    
    // Get Session Endpoint
    public function get_session(){
        echo "Getting a session";
    }
    
    // Session Lookup Endpoint
    public function session_lookup(){
        echo "looking up a session";
    }
}
