<?php

class vehicle_lookup_endpoint_registration {
    private static $instance;
    private $endpoints = [];
    
    private static $prefix = 'public/api';
    
    public static function getInstance(){
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
      }
    
    public function register_endpoint($endpoint, $function){
        $this->endpoints[$endpoint] = $function;
    }
    
    public static function call_endpoint(){
        
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $endpoint = trim(str_replace("/" . self::$prefix . "/", '', $request_uri),"/");
        
        if(isset(self::getInstance()->endpoints[$endpoint])){
            call_user_func(self::getInstance()->endpoints[$endpoint]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'This page does not exist.']);
            exit;
        }
    }
}

// Registration function
function register_endpoint($endpoint, $function){
    if(is_callable($function)){
        vehicle_lookup_endpoint_registration::getInstance()->register_endpoint($endpoint, $function);
        return true;
    }
    
    return false;
}
