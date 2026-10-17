<?php

use function Env\env;

class vehicle_lookup_database {
    
    private static $instance;
    
    public function __construct() {
        if(!self::$instance){
            $host = env('database_host') ?? '127.0.0.1'; 
            $user = env('database_user');
            $database = env('database_name');
            $password = env('database_password');
            
            $connection = new mysqli($host, $user, $password, $database);
            
            if($connection->connect_error){
                throw new Exception('Unable to connect to database.');
            }
            
            self::$instance = $connection;
            
           self::createTables();
        }
    }
    
    public function getConnection(){
        return self::$instance;
    }
    
    public function query($query){
        return self::getConnection()->query($query);
    }
    
    private static function createTables(){
        // Session Data
        $sql = "CREATE TABLE IF NOT EXISTS vehicle_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Vehicle Details
    vrm VARCHAR(10) NOT NULL,
    make VARCHAR(50) DEFAULT NULL,
    model VARCHAR(50) DEFAULT NULL,
    colour VARCHAR(30) DEFAULT NULL,
    
    -- Session Timing (using your exact names)
    session_start DATETIME NOT NULL,
    session_end DATETIME DEFAULT NULL, 
    session_agreed_end DATETIME NOT NULL, 
    
    -- Session Type (permit, free, paid)
    session_type ENUM('permit', 'free', 'paid') NOT NULL DEFAULT 'paid',
    
    -- Additional metadata for status/location
    session_status ENUM('active', 'completed', 'overstay', 'cancelled') DEFAULT 'active',
    location_code VARCHAR(50) DEFAULT 'MAIN_CARPARK',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
       
        
        self::getConnection()->query($sql);
    }
}
