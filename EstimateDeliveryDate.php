<?php
require_once __DIR__ . '/vendor/autoload.php';




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
    static $bankDays = array();
    


    

   
    
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
               
                $range_date['endDate'] = date("Y-m-d"); 
                $range_date['startDate'] = date('Y-m-d', strtotime('-'. $noOfDaysAgo. 'days', strtotime($range_date['endDate'])));
                break;

            case ($startMonth < $currentMonth && $endMonth == ""):
               
                $range_date['startDate'] = self::getStartDate($startMonth);
                $range_date['endDate'] = self::getEndDate($startMonth, $endMonth);
                break;
            
            case ($startMonth == $currentMonth):
                
                $range_date['startDate'] = self::getStartDate($currentMonth);
                $range_date['endDate'] = date("Y-m-d");
                break;
            
            case ($startMonth < $currentMonth && $currentMonth <= $endMonth):  
                
                $range_date['startDate'] = self::getStartDate($startMonth);
                $range_date['endDate'] = self::getEndDate($previousMonth); 
                break;

            case ($startMonth < $endMonth && $endMonth < $currentMonth):  
               
                $range_date['startDate'] = self::getStartDate($startMonth);
                $range_date['endDate'] = self::getEndDate($endMonth); 
                break;

           
        }
        self::StorefromAPI(2021);
        self::createNonWorkingInterval(2021);
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

    static function getHistoricalInterval($zip_code, $orderDate, $historicalInterval, $dbTable){
        $startDate = $historicalInterval['startDate'];
        $endDate = $historicalInterval['endDate'];

        global $conn;
        $interval = array();
        

        $sql = "SELECT * 
                FROM $dbTable 
                WHERE  zip_code = '$zip_code'
                AND shipment_date BETWEEN '$startDate' AND '$endDate'";

        $queryResult = $conn->query($sql);
        
        if ($queryResult->num_rows > 0) {
        
            while($row = $queryResult->fetch_assoc()) {
        
                $interval[] = $row['delivery_interval']; 
        
            }
        }
        return $interval;

    }

    static function calculateEstimatedDeliveryTime($zip_code, $orderDate, $historicalInterval, $dbTable){
        /**
         * Return an estimated interval for a specific zip_code based on order date and historical data 
         * for that zip_code.
         * 
         * @param string $zip_code
         * @param  array $historicalInterval
         * @param  const $dbTable
         * 
         * @return string $estimatedDeliveryTime
         */

        
        $historicalIntervalFromTable = self::getHistoricalInterval($zip_code, $orderDate, $historicalInterval, $dbTable);
      
        $estimatedDeliveryTime = 0;

        foreach($historicalIntervalFromTable as $key => $value){

            $estimatedDeliveryTime +=(int)$value;
        
        }
        
        $estimatedDeliveryTime = round($estimatedDeliveryTime / count($historicalIntervalFromTable),0);
        $estimatedDeliveryDate = date('Y-m-d', strtotime('+'. $estimatedDeliveryTime. 'days', strtotime($orderDate)));
       
        $daysAdded = self::checkBankDays($orderDate, $estimatedDeliveryDate);
        $estimatedDeliveryDate = date('Y-m-d', strtotime('+'. $daysAdded . 'days', strtotime($estimatedDeliveryDate)));
       
        return $estimatedDeliveryDate;
    }

    static function storeFromAPI ($year) {
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
         * Add weekend days
         * 
         * @var int $weekDayNo - weekday number for first day of year
         * @var date $weekendSat - Saturdays date
         */
        
         $weekDayNo = date('N',strtotime(strval($year) . '-01-01'));
        
         //find first saturday
        if($weekDayNo <= 6){
            
            $weekendSat = date('Y-m-d', strtotime('+'. 6-$weekDayNo . 'days', strtotime(strval($year) . '-01-01')));
        }else{
        
            $weekendSat = date('Y-m-d', strtotime('+'. $weekDayNo-1 . 'days', strtotime(strval($year) . '-01-01')));
        }

        // find all saturdays in year
        while ($weekendSat <= date('Y-m-d', strtotime(strval($year). '-12-31'))){
           
            if(!in_array($weekendSat, self::$bankDays)){
        
                self:: $bankDays[] = $weekendSat;
            }
            
            $weekendSat = date('Y-m-d', strtotime('+'. 7 . 'days', strtotime($weekendSat)));
        }

        //find first sunday
        $weekendSun = date('Y-m-d', strtotime('+'. 7-$weekDayNo . 'days', strtotime(strval($year) . '-01-01')));
           
        while ($weekendSun <= date('Y-m-d', strtotime(strval($year) . '-12-31'))){
       
            if(!in_array($weekendSun, self::$bankDays)){
       
                self:: $bankDays[] = $weekendSun;
            }

                $weekendSun = date('Y-m-d', strtotime('+'. 7 . 'days', strtotime($weekendSun)));
        }
    }

    static function checkBankDays($orderDate, $estimatedDeliveryDate){

        $daysAdded = 0;
        foreach(self::$bankDays as $key=>$bankDay){

            if($bankDay >= $orderDate && $bankDay <= $estimatedDeliveryDate){
            
                $daysAdded +=1;
            }
        }
        return $daysAdded;    
    }



    //create record
    static function createRecord(){
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
    
        $shipmentDate = date('Y-m-d', mt_rand(1514764800, 1609372800));
        echo $shipmentDate ;

        $deliveryTime = mt_rand(3, 14);
        echo('<br>');
        echo $deliveryTime;

        $deliveryDate = date('Y-m-d', strtotime('+'. $deliveryTime . 'days', strtotime($shipmentDate)));

        echo('<br>');
        echo $deliveryDate;
        
        for($year=2018; $year<=2020; $year++){
            self::storeFromAPI($year) ;
            self::createNonWorkingInterval($year);
        }


        arsort(self::$bankDays);
        print_r(self::$bankDays);

        $daysAdded = self::checkBankDays($shipmentDate, $deliveryDate);
        $deliveryDate = date('Y-m-d', strtotime('+'. $daysAdded . 'days', strtotime($deliveryDate)));
        echo('<br>');
        echo $deliveryDate  ;

}

}

    
 
    
?>