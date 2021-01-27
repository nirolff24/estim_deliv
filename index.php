<?php
$file_root_path = $_SERVER["DOCUMENT_ROOT"] . "/";

require $file_root_path . "EstimateDeliveryDate.php";


//EstimateDeliveryDate::getDates();
$result1 = EstimateDeliveryDate::readDateInterval("10","","");

$result2 = EstimateDeliveryDate::readDateInterval("10","October-2010","March-2021");
$result3 = EstimateDeliveryDate::readDateInterval("","","March-2021");
$result4 = EstimateDeliveryDate::readDateInterval("","October-2010","");
$result5 = EstimateDeliveryDate::readDateInterval("","October-2010","March-2021");
$result6 = EstimateDeliveryDate::readDateInterval("","October-2010","November-2020");
$result7 = EstimateDeliveryDate::readDateInterval("","October-2010","January-2021");
$result8 = EstimateDeliveryDate::readDateInterval("","January-2021","March-2021");
$result9 = EstimateDeliveryDate::readDateInterval("","January-2021","");
$result10 = EstimateDeliveryDate::readDateInterval("","January-2021","January-2021");
$result11= EstimateDeliveryDate::readDateInterval("","","October-2010");
$result12= EstimateDeliveryDate::readDateInterval("","","January-2021");

print_r($result1); 
echo('<br>');
print_r($result2); 
echo('<br>'); 
print_r($result3); 
echo('<br>'); 
print_r($result4); 
echo('<br>'); 
print_r($result5); 
echo('<br>'); 
print_r($result6); 
echo('<br>'); 
print_r($result7); 
echo('<br>'); 
print_r($result8); 
echo('<br>'); 
print_r($result9); 
echo('<br>'); 
print_r($result10); 
echo('<br>'); 
print_r($result11); 
echo('<br>'); 
print_r($result12); 
echo('<br>');  
