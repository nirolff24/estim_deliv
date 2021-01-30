<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
$file_root_path = $_SERVER["DOCUMENT_ROOT"] . "./";
require $file_root_path . "EstimateDeliveryDate.php";

final class EstimateDeliveryDateSqlTest extends TestCase
{
    

    public function testgetHistoricalInterval(): void
    {
        $file_root_path = $_SERVER["DOCUMENT_ROOT"] . "./";
        require $file_root_path . "database_config.php";
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
        !$this->assertEmpty( EstimateDeliveryDate::getHistoricalInterval(30116,'2021-01-20', array('2020-01-01','2020-10-31'),'historical_data'),'Array is not empty!');
    }

    
}