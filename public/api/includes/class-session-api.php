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
//        return true;
        
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
        
        $account = self::$database->query($query, [$apiKey], true);
        
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
    
    private function reject_request($error_message, $code = '404'){
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $error_message]);
        exit;
    }
    
    // Register Session Endpoint
    public function register_session(){
        $method = $_SERVER['REQUEST_METHOD'];
        if($method !== 'POST'){
            $this->reject_request('Incorrect method used.', '405');
        }
        
        // Ensure all required fields are present
        $required_fields = [
            'vrm',
            'make',
            'model',
            'colour',
            'session_start',
            'session_agreed_end',
            'session_type',
            'location_code'
        ];
        
        foreach($required_fields as $key => $field_key){
            // Remove present fields to leave a list of missing values
            if(isset($_POST[$field_key]) && $_POST[$field_key]){
                unset($required_fields[$key]);
            }
        }
        
        if($required_fields){
            $missing_fields = implode(", ", $required_fields);
            $this->reject_request("Missing fields: [{$missing_fields}]", 400);
        }
        
        // Prepare data and insert into database
        $query = "INSERT INTO vehicle_sessions 
        (vrm, make, model, colour, session_start, session_end, session_agreed_end, session_type, session_status, location_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query_params = [
            'vrm' => $_POST['vrm'],
            'make'=> $_POST['make'],
            'model'=> $_POST['model'],
            'colour' => $_POST['colour'],
            'session_start'=> strtotime($_POST['session_start']),
            'session_end'=> isset($_POST['session_end']) ? strtotime($_POST['session_end']) : null,
            'session_agreed_end'=> strtotime($_POST['session_agreed_end']),
            'session_type'=> $_POST['session_type'],
            'session_status' => $this->get_session_status($_POST),
            'location_code'=> $_POST['location_code'],
//            'created_at' => time()
        ];
        
        $query_params = $this->validate_data($query_params);
        
        $sql_params = array_values($query_params);
        
        // Submit to Database
        $response = self::$database->query($query, $sql_params);
        
        if($response){
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Vehicle session registered successfully.',
                'data' => array_merge([
                    'id' => $response,
                    ], 
                    $query_params
                    )
            ]);
            exit;
        } else{
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save session to database.']);
            exit;
        }
    }
    
    // End Session Endpoint
    public function end_session(){
        $method = $_SERVER['REQUEST_METHOD'];
        if($method !== 'POST'){
            $this->reject_request('Incorrect method used.', '405');
        }
        
         // Ensure all required fields are present
        $required_fields = [
            'id',
            'session_end'
        ];
        
        foreach($required_fields as $key => $field_key){
            // Remove present fields to leave a list of missing values
            if(isset($_POST[$field_key]) && $_POST[$field_key]){
                unset($required_fields[$key]);
            }
        }
        
        if($required_fields){
            $missing_fields = implode(", ", $required_fields);
            $this->reject_request("Missing fields: [{$missing_fields}]", 400);
        }
        
        if(is_nan($_POST['id'])){
            $this->reject_request("ID value is non numeric", 400);
        }
        
        $session_end = strtotime($_POST['session_end']);
        $current_query = "SELECT id, vrm, make, model, session_start, session_end, session_agreed_end FROM vehicle_sessions WHERE id = ?";
        $current_data = self::$database->query($current_query, [$_POST['id']], true);
        
        if($current_data['session_end'] == $session_end){
            $this->reject_request("Session end already registered.", 409);
        }
        
        // Prepare data and update session
        $sql_query = "UPDATE vehicle_sessions SET session_end = ?, session_status = ? WHERE id = ?";
        
        //Set Current End
        $current_data['session_end'] = $session_end;
        
        $query_params = [
            'session_end' => $current_data['session_end'],
            'session_status' => $this->get_session_status($current_data), 
            'id' => $_POST['id']
        ];
        
        $query_params = $this->validate_data($query_params);
        
        $sql_params = array_values($query_params);
        
        // Submit to Database
        $response = self::$database->query($sql_query, $sql_params, true);
        
        $current_data = array_merge($current_data, $query_params);
        
        if($response){
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Vehicle session updated successfully.',
                'data' => $current_data
            ]);
            exit;
        } else{
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save session to database.']);
            exit;
        }
    }
    
    // Get Session Endpoint
    public function get_session(){
        echo "Getting a session";
    }
    
    // Session Lookup Endpoint
    public function session_lookup(){
        echo "looking up a session";
    }
    
    /**
    * Additional Functions
    */
    // Get session status
    private function get_session_status($session){
        $current_time = time();
        $session_agreed_end = strtotime($session['session_agreed_end']);
       
        //Current session completed
        if(!empty($session['session_end'])){
            return 'completed';
        }
        
        // Current session is overstaying
        if($session_agreed_end && $current_time > $session_agreed_end){
            return 'overstay';
        }
        
        // Current session is on going
        return 'active';
    }
    
    // Validate API data
    private function validate_data($data){
        $allow_null = ['session_end'];
        foreach($data as $key => $data_item){
            if(!$data_item && !in_array($key, $allow_null)){
                $this->reject_request("Malformed Data: $key", 400);
            }
        }
        
        return $data;
    }
}
