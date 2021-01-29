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


$historicalInterval = EstimateDeliveryDate::readDateInterval("","January-2020","January-2021");
print_r($historicalInterval);
$zip_code=30116;
$orderDate = date('Y-m-d', strtotime('m'));


$estimatedDeliveryDate = EstimateDeliveryDate::calculateEstimatedDeliveryDate($zip_code, $orderDate, $historicalInterval, DB_TABLE);

echo('Order date is: ' . $orderDate);
echo('<br>');
echo('Delivery date is: ' . $estimatedDeliveryDate);




/**============Fill historical table=================== */
// for($year=2018; $year<=2020; $year++){
//     EstimateDeliveryDate::storeFromAPI($year) ;
//     EstimateDeliveryDate::createNonWorkingInterval($year);
// }

// for ($i=1; $i<=1000; $i++){
//     EstimateDeliveryDate::createRecord();
// }
/**==================================================== */
