<?php

use function Env\env;

class vehicle_lookup_database {
    
    private static $connection;
    
    public function __construct() {
       
    }
    
    public static function getConnection(){
        if(!self::$connection){
            $host     = $_ENV['DB_HOST'] ?? '127.0.0.1'; 
            $user     = $_ENV['DB_USER'] ?? '';
            $database = $_ENV['DB_NAME'] ?? '';
            $password = $_ENV['DB_PASS'] ?? '';
            
            $connection = new mysqli($host, $user, $password, $database);
            
            if($connection->connect_error){
                throw new RuntimeException('Unable to connect to database.');
            }
            
            self::$connection = $connection;
            
           self::createTables();
        }
        return self::$connection;
    }
    
    public function query($statement, $params = []){
        
        // Fetch Query with Params
        if($params){
            $command = self::getConnection()->prepare($statement);
            $command->execute($params);
            $result = $command->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $command->close();
            
            return $data;
        }
        
        // Fetch Standard Query
        $result = self::getConnection()->query($statement);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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
        
        // API Key Registration
        $sql = "CREATE TABLE IF NOT EXISTS api_keys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(150) NOT NULL,
        api_key VARCHAR(64) NOT NULL UNIQUE,
        api_secret VARCHAR(64) NOT NULL,
        allowed_ip VARCHAR(45) DEFAULT NULL -- Not Implemented but recommended for vehicle_lookup system
        )";
        
        self::getConnection()->query($sql);
    }
}
