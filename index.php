<?php
$file_root_path = $_SERVER["DOCUMENT_ROOT"] . "/";
require $file_root_path . "database_config.php";
require $file_root_path . "EstimateDeliveryDate.php";

global $conn;

function connect_db() {
	    global $conn;

        $servername = DB_HOSTNAME;
        $username = DB_USERNAME;
        $password = DB_PASSWORD;
        $dbname = DB_DATABASE;
        
      
        //Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        //Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        // } 
    };
}


//echo (strtotime('2021-01-27'));
$historicalInterval = EstimateDeliveryDate::readDateInterval("10","","");


// print_r($historicalInterval); 
// echo('<br>');

$zip_code=30116;
$orderDate = date(2021-01-17);
connect_db();
$estimatedDeliveryDate = EstimateDeliveryDate::calculateEstimatedDeliveryTime($zip_code, $orderDate, $historicalInterval);

 echo($estimatedDeliveryDate);