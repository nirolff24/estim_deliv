<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
require $_SERVER["DOCUMENT_ROOT"] . "./" . "EstimateDeliveryDate.php";
require $_SERVER["DOCUMENT_ROOT"] . "./" . "database_config.php";
final class EstimateDeliveryDateSqlTest extends TestCase
{
    

    public function testgetHistoricalInterval(): void
    {
       
        
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
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'max_values',
            'noOfDaysAgo' => "100", 
            'startMonth' => "", 
            'endMonth' => ""
        );
        !$this->assertEquals(318, count(EstimateDeliveryDate::getHistoricalInterval($input, array('startDate'=>'2020-01-01','endDate'=>'2020-10-31'))));
    }

    
}