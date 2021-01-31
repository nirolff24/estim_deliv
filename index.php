<?php

require __DIR__ . '/EstimateDeliveryDate.php';








// Select 30116 or 30216 or 30316 or 30416 or 30516
$input = array(
                'zipCode' => 30116, 
                'orderDate' => date('Y-m-d', strtotime('m')),
                'meanOption' => 'max_values',
                'noOfDaysAgo' => "", 
                'startMonth' => "2018-03", 
                'endMonth' => "2019-05"
            );

$estimatedDeliveryDate = EstimateDeliveryDate::calculateEstimatedDeliveryDate($input);






/**============Fill historical table=================== */
// for($year=2018; $year<=2021; $year++){
//     EstimateDeliveryDate::storeFromAPI($year) ;
//     EstimateDeliveryDate::createNonWorkingInterval($year);
// }

// for ($i=1; $i<=1000; $i++){
//     EstimateDeliveryDate::createRecord();
// }
/**==================================================== */
