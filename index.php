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


$historicalInterval = EstimateDeliveryDate::readDateInterval("","2020-1","");
echo("Date interval for analysis: <br>");
echo('Start date: ' . $historicalInterval['startDate'] . '<br>');
echo('End date: ' . $historicalInterval['endDate'] . '<br>');

$zip_code=30116; // Select 30116 or 30216 or 30316 or 30416 or 30516
$orderDate = date('Y-m-d', strtotime('m'));
EstimateDeliveryDate::$mean_option = 'max_values'; //or 'average'

$estimatedDeliveryDate = EstimateDeliveryDate::calculateEstimatedDeliveryDate($zip_code, $orderDate, $historicalInterval, DB_TABLE);

echo('Order date is: ' . $orderDate);
echo('<br>');
echo('Delivery date is: ' . $estimatedDeliveryDate);




/**============Fill historical table=================== */
// for($year=2018; $year<=2021; $year++){
//     EstimateDeliveryDate::storeFromAPI($year) ;
//     EstimateDeliveryDate::createNonWorkingInterval($year);
// }

// for ($i=1; $i<=1000; $i++){
//     EstimateDeliveryDate::createRecord();
// }
/**==================================================== */
