<?php

class EstimateDeliveryDate {

    /**
     * 
     * 
     * @var $zip_code
     * @var $shipment_date
     * @var $delivery_date
     * @var $start_date
     * @var $end_date
     * @var $delivery_interval  //working days
     */
   // require  "database_config.php"; 
    static $zip_code;
    static $shipment_date;
    static $delivery_date;
    static $start_date;
    static $end_date;
    static $delivery_interval;
    static $file_root_path;
    


    

   
    
    static function readDateInterval( $noOfDaysAgo, $startMonth, $endMonth){
        /**
         * Read from input either number of day ago to be added to current date,
         * or a start month with or without and end month.
         * If end month is omitted the start date is the first day of start month and 
         * end date is the last date of the previous month.
         * If end month is provided then the end date is the last day of the end month.
         * 
         * @param $noOfDaysAgo
         * @param $startMonth
         * @param $endMonth
         * 
         * 
         *  */        
        $currentMonth = date('Y-F', strtotime('m'));
        $startMonth = ($startMonth) ? date('Y-F', strtotime($startMonth)) : $currentMonth;
        $endMonth = ($endMonth) ? date('Y-F', strtotime($endMonth)) : $currentMonth;
        $firstDateCurrentMonth = date('Y-m-01', strtotime('m'));
        $previousMonth = date('Y-F', strtotime('-1month', strtotime($firstDateCurrentMonth)));
        $date_range= array();


        switch (true){

            case ($noOfDaysAgo > 0):  
                echo(5);
                $range_date['endDate'] = date("Y-m-d"); 
                $range_date['startDate'] = date('Y-m-d', strtotime('-'. $noOfDaysAgo. 'days', strtotime($range_date['endDate'])));
                break;

            case ($startMonth < $currentMonth && $endMonth == ""):
                echo(1);
                $range_date['startDate'] = self::getStartDate($startMonth);
                $range_date['endDate'] = self::getEndDate($startMonth, $endMonth);
                break;
            
            case ($startMonth == $currentMonth):
                echo(2);
                $range_date['startDate'] = self::getStartDate($currentMonth);
                $range_date['endDate'] = date("Y-m-d");
                break;
            
            case ($startMonth < $currentMonth && $currentMonth <= $endMonth):  
                echo(3);
                $range_date['startDate'] = self::getStartDate($startMonth);
                $range_date['endDate'] = self::getEndDate($previousMonth); 
                break;

            case ($startMonth < $endMonth && $endMonth < $currentMonth):  
                echo(4);
                $range_date['startDate'] = self::getStartDate($startMonth);
                $range_date['endDate'] = self::getEndDate($endMonth); 
                break;

           
        }

        return $range_date;
    }
   
    
    static function getStartDate($startMonth){
        /**
         * 
         * @return $start_date  Start date of analysis interval
         */  

        $startDate = date('Y-m-01', strtotime($startMonth));

        return $startDate;
    }


    static function getEndDate($endMonth){
        /**
         * 
         * 
         * @return self::$end_date  End date of analysis interval
         * */  

        $endDate = date('Y-m-t',strtotime($endMonth));

        return $endDate;
            
        }

    static function getQueryFromTable(){
        /**
         *
         * @param $zip_code
         * @return $sql  query with data about a specific zip_code 
         */
    }


    
 // function connect_db() {
	//     global $conn;

    //     // $servername = DB_HOSTNAME;
    //     // $username = DB_USERNAME;
    //     // $password = DB_PASSWORD;
    //     // $dbname = DB_DATABASE;
        
    //     // Create connection
    //     // $conn = new mysqli($servername, $username, $password, $dbname);
        
    //     // Check connection
    //     // if ($conn->connect_error) {
    //     //     die("Connection failed: " . $conn->connect_error);
    //     // } 
    // };

?>