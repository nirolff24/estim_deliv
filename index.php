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
        };
}

connect_db();


// $historicalInterval = EstimateDeliveryDate::readDateInterval("100","","");
// $zip_code=30116;
// $orderDate = date('Y-m-d',strtotime('2021-01-17'));

// $estimatedDeliveryDate = EstimateDeliveryDate::calculateEstimatedDeliveryTime($zip_code, $orderDate, $historicalInterval, DB_TABLE);

// echo('Order date is: ' . $orderDate);
// echo('<br>');
// echo('Delivery date is: ' . $estimatedDeliveryDate);


EstimateDeliveryDate::createRecord();
