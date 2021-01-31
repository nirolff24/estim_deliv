<?php
require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ .  '/database_config.php';



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
    static $zip_code;
    static $shipment_date;
    static $delivery_date;
    static $start_date;
    static $end_date;
    static $delivery_interval;
    static $file_root_path;
    static $bankDays = array();
    static $mean_option;
    static $conn;
   
    



    static function connect_db() {
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


    static function calculateEstimatedDeliveryDate($input){
        /**
         * Return an estimated delivery date for a specific zip_code based on order date and historical data 
         * for that zip_code.
         * 
         * @param array $input
         * 
         * @var int $daysAdded - no of day to be added based on check if there are weekend days in 
         * time interval between order date and initial estimated delivery date  
         * 
         * @return date $estimatedDeliveryDate
         */
        
        $orderDate = $input['orderDate'];
        self::connect_db();

        /**return interval of dates for analysis, start date and end date */
        $historicalInterval = self::readDateInterval($input);

        /** return an array with values from table historical_data */
        $historicalIntervalFromTable = self::getHistoricalInterval($input, $historicalInterval);
        
        echo(count($historicalIntervalFromTable));
       
        $estimatedDeliveryDays= self::estimateBasedOnHistoricData($input, $historicalIntervalFromTable);
        
        /** initially estimated delivery date(calendar days) */
        $estimatedDeliveryDateInitial = date('Y-m-d', strtotime('+'. $estimatedDeliveryDays. 'days', strtotime($orderDate))); //initial estimated delivery date
       
        $daysAdded = self::checkBankDays($orderDate, $estimatedDeliveryDateInitial);
        echo('<br>');
        echo('Working days to add:' . $estimatedDeliveryDays  .'<br>');
       
       /** finally estimated delivery date (working days) */
        $estimatedDeliveryDateFinal = date('Y-m-d', strtotime('+'. $daysAdded . 'days', strtotime($estimatedDeliveryDateInitial)));  //final estimated delivery date
       
        echo('Order date is: ' . $orderDate);
        echo('<br>');
        echo('Delivery date is: ' . $estimatedDeliveryDateFinal);
        
        return $estimatedDeliveryDate;
    }

    static function readDateInterval( $input){
        /**
         * Read from input either number of day ago to be added to current date,
         * or a start month with or without and end month.
         * If end month is omitted the start date is the first day of start month and 
         * end date is the last date of the previous month.
         * If end month is provided then the end date is the last day of the end month.
         * 
         * @param $input
         * 
         * @return array $rangeDate - start and end date for analysis interval to be selected from historical table 
         *  */        
        $currentMonth = date('Y-m', strtotime('m'));
        $firstDateCurrentMonth = date('Y-m-01', strtotime('m'));
        $previousMonth = date('Y-m', strtotime('-1month', strtotime($firstDateCurrentMonth)));
        $startMonth = $input['startMonth'];
        $noOfDaysAgo =  $input['noOfDaysAgo'];
        $endMonth = $input['endMonth'];
        $rangeDate= array();

        if(!($startMonth) && $noOfDaysAgo ==""){
            exit('Please select a starting month!');
        } 
        

        if(!self::checkInputStartMonth($startMonth, $currentMonth)){           
            exit('Select a starting month earlier than current month!');
        }
        
        if(!self::checkInputEndMonth($startMonth, $endMonth)){
            exit('Start month must be earlier than end month');
        }

        switch (true){
            case ($noOfDaysAgo > 0):  
                /**
                 * calculate interval based on current date and no. of days ago
                 */
                $rangeDate['endDate'] = date("Y-m-d"); 
                $rangeDate['startDate'] = date('Y-m-d', strtotime('-'. $noOfDaysAgo. 'days', strtotime($rangeDate['endDate'])));
                break;

            case ($startMonth < $currentMonth && !($endMonth) ):
                /**
                 * calculate interval based only on start month
                 */
                $rangeDate['startDate'] = self::getStartDate($startMonth);
                $rangeDate['endDate'] = self::getEndDate($startMonth);
                break;
            
            case ($startMonth < $endMonth && $endMonth < $currentMonth):  
                    /**
                    * calculate interval if both start month and end month are in the past
                    */
                   $rangeDate['startDate'] = self::getStartDate($startMonth);
                   $rangeDate['endDate'] = self::getEndDate($endMonth); 
                   break;

            case ($startMonth < $currentMonth && $currentMonth < $endMonth):  
                    /**
                    * calculate interval if selected start month is in the past and selected end month is in future or current month
                    * then the end date is end date of previous month
                    */
                    $rangeDate['startDate'] = self::getStartDate($startMonth);
                    $rangeDate['endDate'] = self::getEndDate($previousMonth); 
                    break;
        }
        echo("Date interval for analysis: <br>");
        echo('Start date: ' . $rangeDate['startDate'] . '<br>');
        echo('End date: ' . $rangeDate['endDate'] . '<br>');
        
        self::StorefromAPI(2021);
        self::createNonWorkingInterval(2021);

        return $rangeDate;
    }
   
    
    static function getStartDate($startMonth){
        /**
         * Return start date of analysis interval
         * @param date $startMonth
         * @return date $start_date  
         */  

        $startDate = date('Y-m-01', strtotime($startMonth));

        return $startDate;
    }


    static function getEndDate($endMonth){
        /**
         * 
         * Return end date of analysis interval
         * @param date $endMonth;
         * @return date $end_date  
         * */  

        $endDate = date('Y-m-t',strtotime($endMonth));

        return $endDate;
            
        }

   

    static function getHistoricalInterval($input, $historicalInterval){

        /**
         * Query data from historical table based on zip code and analysis interval ($historicalInterval)
         * @param array $input
         * 
         *  
         * @return array $interval - an array of delivery times 
         */
        $zip_code = $input['zipCode'];
        $startDate = $historicalInterval['startDate'];
        $endDate = $historicalInterval['endDate'];
        global $conn;
        $interval = array();

         if(!self::checkInputZipCode($zip_code)){
            echo('Entered zip_code is not in those mentioned!');
            exit();
        };

        $sql = "SELECT * 
                FROM " . DB_TABLE 
                . " WHERE  zip_code = '$zip_code'
                AND shipment_date BETWEEN '$startDate' AND '$endDate'";

        $queryResult = $conn->query($sql);

        if ($queryResult->num_rows > 0) {
        
            while($row = $queryResult->fetch_assoc()) {
        
                $interval[] = intval($row['delivery_work_interval']); 
        
            }

        }else{
            echo('Either the selected zip code was never delivered to nor the interval for analysis contains any data!<br>
                  Maximum deliver time of 14 days will be used.');
                  $interval[]=14;
        }
    
        
        return $interval;

    }

    

    static function storeFromAPI ($year) {
        /**
         * read an API for RO bank days (as example)
         * 
         * @param date $year - year to get bank days for
         * @return array $bankDays
         */

        try {
            $apiUrl = 'https://zilelibere.webventure.ro/api/'.$year;
            $dataUrl = json_decode(file_get_contents($apiUrl, true), true);
           
            foreach($dataUrl as $dateUrl) {
                
                $name = $dateUrl['name'];
               
                foreach($dateUrl['date'] as $value){
                    
                    self:: $bankDays[] = date('Y-m-d', strtotime($value['date']));
                }

            }
        } catch (\Throwable $th) {

           echo('A aparut o eroare...' . $th);
        }
       
    }
 
    static function createNonWorkingInterval($year){
        /**
         * Add weekend days to bankDays array based on a selected year
         * 
         * @var int $weekDayNo - weekday number for first day of year
         * @var date $weekendSat - Saturdays date
         * @var date $weekendSun - Sunday date
         */
        
         $weekDayNo = date('N',strtotime(strval($year) . '-01-01'));
        
         //find first saturday
        if($weekDayNo <= 6){
            
            $weekendSat = date('Y-m-d', strtotime('+'. 6-$weekDayNo . 'days', strtotime(strval($year) . '-01-01')));
        }else{
        
            $weekendSat = date('Y-m-d', strtotime('+'. $weekDayNo-1 . 'days', strtotime(strval($year) . '-01-01')));
        }

        // find all saturdays in year and add to array bankDays
        while ($weekendSat <= date('Y-m-d', strtotime(strval($year). '-12-31'))){
           
            if(!in_array($weekendSat, self::$bankDays)){
        
                self:: $bankDays[] = $weekendSat;
            }
            
            $weekendSat = date('Y-m-d', strtotime('+'. 7 . 'days', strtotime($weekendSat)));
        }

        //find first sunday
        $weekendSun = date('Y-m-d', strtotime('+'. 7-$weekDayNo . 'days', strtotime(strval($year) . '-01-01')));
        //find all sundays in year and add to array bankDays  
        while ($weekendSun <= date('Y-m-d', strtotime(strval($year) . '-12-31'))){
       
            if(!in_array($weekendSun, self::$bankDays)){
       
                self:: $bankDays[] = $weekendSun;
            }

                $weekendSun = date('Y-m-d', strtotime('+'. 7 . 'days', strtotime($weekendSun)));
        }
    }

    static function checkBankDays($orderDate, $estimatedDeliveryDate){
        /**
         * Check if there are bank days or weekend days between order date and initial estimated delivery date
         * 
         * @param date $orderDate
         * @param date $estimateddeliveryDate
         * 
         * @return int $daysAdded - number of days to be added to initila estimated delivery date
         */

        $daysAdded = 0;
        foreach(self::$bankDays as $key=>$bankDay){

            if($bankDay >= $orderDate && $bankDay <= $estimatedDeliveryDate){
            
                $daysAdded +=1;
            }
        }
        return $daysAdded;    
    }

    static function estimateBasedOnHistoricData($input, $historicalIntervalFromTable){
        /**
         * Estimate a delivery time based on historical data
         * 
         * @param array input
         * @param array $historicalIntervalFromTable 
         * 
         * If option is 'average' then the estimated delivery time is arithmetic mean of all value selected
         * If option is 'max values' the the estimated time time is arithmetic mean of all values with occurence number between max*0.8 and max
         * 
         * 
         * 
         */

        $option = $input['meanOption'];
         switch ($option){
            case 'average':
                foreach($historicalIntervalFromTable as $key => $value){

                    $estimatedDeliveryDays +=(int)$value;
                }
                $estimatedDeliveryDays = round($estimatedDeliveryDays / count($historicalIntervalFromTable),0);
                break;

            case 'max_values':
                $arrCounted = array_count_values($historicalIntervalFromTable);
                $resultArray = array();
                foreach ($arrCounted as $key => $val){
                    if ($val >= max($arrCounted)*0.8 &&  $val <= max($arrCounted)) {
                        $resultArray[$key] = $val;
                        $estimatedDeliveryDays += $key;
                    }
                }
                
                $estimatedDeliveryDays = round($estimatedDeliveryDays / count($resultArray), 0);
                break;
        }
        
        return $estimatedDeliveryDays;

    }

    static function checkInputZipCode($zip_code){
        /**
         * 
         * Check if values are ok and return boolean
         * 
         * @return boolean
         * 
         */
        $zipCodesInterval = array(
            '30116'=>1,
            '30216'=>2,
            '30316'=>3,
            '30416'=>4,
            '30516'=>5
          );
        return (array_key_exists($zip_code, $zipCodesInterval));
       
    }

    
    static function checkInputStartMonth($startMonth, $currentMonth){
        /**
         * 
         * Check if start date is earlier than current month
         * 
         * @return boolean
         * 
         */
       
       

        return ($startMonth < $currentMonth);
    }

    static function checkInputEndMonth($startMonth, $endMonth){
        /**
         * 
         * Check that endMonth should be later than start month, when they are entered
         * 
         * @return boolean
         * 
         */
       
         if(!($endMonth)){
            return TRUE;
        }

        return ( ($startMonth != $endMonth) && ($startMonth < $endMonth) );
    }



/**============Fill historical table=================== 
 * these functions were used to populate the historical table 
 */   
    private static function createRecord(){
        //@zipCode
        //@shipmentDate
        //@$deliveryDate
        //@deliveryTime
        //@nonWorkingInterval
        //generate random zip code from interval ( 5 zip codes)
        //generate random shipment date from interval (01.01.2018 - 31.12.2020)
        //generate random delivery interval (3-14)
        //calculate delivery date
        //store in table

        $zipCodesInterval = array(
                                  '30116'=>1,
                                  '30216'=>2,
                                  '30316'=>3,
                                  '30416'=>4,
                                  '30516'=>5
                                );
        $zipCode =  array_rand($zipCodesInterval, 1);
        echo $zipCode;
    
        $shipmentDate1 = date('Y-m-d', mt_rand(1514764800, 1620372800));
        echo('<br>');
        echo $shipmentDate1 ;
        $shipmentDate2 = self::checkIfWeekend($shipmentDate1);
        echo('<br>');
        echo $shipmentDate2 ;

        $deliveryTime = mt_rand(3, 14);
        echo('<br>');
        echo $deliveryTime;

        $deliveryDate1 = date('Y-m-d', strtotime('+'. $deliveryTime . 'days', strtotime($shipmentDate2)));

        echo('<br>');
        echo $deliveryDate1;
        
        arsort(self::$bankDays);

        $daysAdded = self::checkBankDays($shipmentDate2, $deliveryDate1);
        $deliveryDate2 = date('Y-m-d', strtotime('+'. $daysAdded . 'days', strtotime($deliveryDate1)));
        $deliveryDate3 = self::checkIfWeekend($deliveryDate2);

        echo('<br>');
        echo $daysAdded  ;
        echo('<br>');
        echo $deliveryDate3  ;
        
        self::insertHistoricalTable($zipCode, $shipmentDate2, $deliveryDate3, $deliveryTime, DB_TABLE);
    }
    
    private static function insertHistoricalTable($zipCode, $shipmentDate, $deliveryDate, $deliveryTime, $dbTable){

        /**
         * 
         * Update table historical_data
         * 
         * 
         * 
         */

        global $conn;

        $sql = "INSERT INTO  $dbTable (zip_code, shipment_date, delivery_date, delivery_work_interval) 
                VALUES ($zipCode, '$shipmentDate', '$deliveryDate', $deliveryTime)";

       //echo $sql;
        if($conn->query($sql)){
            
            
        };
    }

    private static function checkIfWeekend($date){
        /**
         * Check if date is weekend and slide it with 1 or 2 day consequently;
         * 
         */
        $weekDayNo = date('N',strtotime($date));
        
        if($weekDayNo == 6){
            
            $date2 = date('Y-m-d', strtotime('+2days', strtotime($date)));

        }elseif($weekDayNo == 7){
        
            $date2 = date('Y-m-d', strtotime('+1days', strtotime($date)));
        }else{
            return $date;
        }
        return $date2;
    }
}

    
 
    
?>