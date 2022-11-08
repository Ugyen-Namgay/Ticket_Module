<?php

//namespace dbconnect;

DEFINE('BASE_URL','http://127.0.0.1/');
$settings = parse_ini_file("settings/config.ini", true);

DEFINE ('DB_USER', $settings["db"]["username"]);
DEFINE ('DB_PSWD', $settings["db"]["password"]); 
DEFINE ('DB_HOST', $settings["db"]["url"]); 
DEFINE ('DB_NAME', $settings["db"]["database"]); 


try {
    $conn = new mysqli(DB_HOST,DB_USER,DB_PSWD);

}
catch (exception $e) {
    header( 'HTTP/1.0 500 Internal Server Error', TRUE, 500 );
    die( header( 'location: error-500' ) );
}

if($conn->connect_error) {
    header( 'HTTP/1.0 500 Internal Server Error', TRUE, 500 );
    die( header( 'location: error-500' ) );
}
