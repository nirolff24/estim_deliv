

<html>
    <body>
    <form action="/index.php" method="get">
        <label for="zipCode">Enter a zipcode:</label>
        <input type="text" name="zipCode" id="zipCode" placeholder="ex:30116">  Select 30116 or 30216 or 30316 or 30416 or 30516
        <br>
        <label for="whatToUse">Select what to use</label>
        <select name="whatToUse" id="whatToUse">
            <option value="noOfDays">Use number of days ago</option>
            <option value="month">Use start month w/o end month</option>
        </select>
        <br>
        <label for="noOfDaysAgo">Enter a number of days:</label>
        <input type="text" name="noOfDaysAgo" id="noOfDaysAgo" placeholder="ex:100">
        <br>
        <label for="startMonth">Enter start month:</label>
        <input type="datetime" name="startMonth" id="startMonth" placeholder="ex:2020-08">
        <br>
        <label for="endMonth">Enter end month:</label>
        <input type="datetime" name="endMonth" id="endMonth" placeholder="ex:2020-08">
        <br>
        <label for="meanOption">Select estimation method</label>
        <select name="meanOption" id="meanOption">
            <option value="average">average</option>
            <option value="max_values">max_value</option>
        </select>
        <br>
       <input type="hidden"  name="method" value="calculateEstimatedDeliveryDate">
        <input type="submit" value="Estimeaza">
    </form>

    <form action="">
        <input type="submit" value="Refresh">
    </form>
        
    </body>
</html>

<?php


require_once  $_SERVER["DOCUMENT_ROOT"] . '/EstimateDeliveryDate.php';
  $estimateDate = new EstimateDeliveryDate;
  $estimateDate->calculateEstimatedDeliveryDate() ;







/**============Fill historical table=================== */
// for($year=2018; $year<=2021; $year++){
//     EstimateDeliveryDate::storeFromAPI($year) ;
//     EstimateDeliveryDate::createNonWorkingInterval($year);
// }

// for ($i=1; $i<=1000; $i++){
//     EstimateDeliveryDate::createRecord();
// }
/**==================================================== */
?>
