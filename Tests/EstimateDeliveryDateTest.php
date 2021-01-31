<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require __DIR__ . '/../EstimateDeliveryDate.php';

final class EstimateDeliveryDateTest extends TestCase
{
    public function testReadDateInterval1(): void
    {
        $date_range1 = array(
            'startDate'=>'2021-01-21',
            'endDate'=>'2021-01-31'
        );
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'max_values',
            'noOfDaysAgo' => "10", 
            'startMonth' => "", 
            'endMonth' => ""
        );
        $this->assertEquals($date_range1, EstimateDeliveryDate::readDateInterval($input));
    }

    public function testReadDateInterval2(): void
    {
        $date_range2 = array(
           
            'startDate'=>'2020-10-01',
             'endDate'=>'2020-10-31'
        );
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'max_values',
            'noOfDaysAgo' => "", 
            'startMonth' => "2020-10", 
            'endMonth' => ""
        );
        $this->assertEquals($date_range2, EstimateDeliveryDate::readDateInterval($input));
    }

    public function testReadDateInterval3(): void
    {
       
        $date_range3 = array(
            
            'startDate'=>'2018-03-01',
            'endDate'=>'2019-05-31'
        );
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'max_values',
            'noOfDaysAgo' => "", 
            'startMonth' => "2018-03", 
            'endMonth' => "2019-05"
        );
        $this->assertEquals($date_range3, EstimateDeliveryDate::readDateInterval($input));
    }

    

    public function testReadDateInterval5(): void
    {
        $date_range5 = array( 
            
            'startDate'=>'2020-01-01',
            'endDate'=>'2020-12-31'
        );
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'max_values',
            'noOfDaysAgo' => "", 
            'startMonth' => "2020-01", 
            'endMonth' => "2021-10"
        );
        $this->assertEquals($date_range5, EstimateDeliveryDate::readDateInterval($input));
    }

    public function testCheckInputStartMonth1(): void
    {
       
        $this->assertEquals(TRUE, EstimateDeliveryDate::checkInputStartMonth("2020-1","2021-01"));
    }
    
    public function testCheckInputStartMonth2(): void
    {
       
        $this->assertEquals(FALSE, EstimateDeliveryDate::checkInputStartMonth("2023-1","2021-01"));
    }
    
    public function testCheckInputEndMonth1(): void
    {
       
        $this->assertEquals(TRUE, EstimateDeliveryDate::checkInputEndMonth("2020-1","2021-01"));
    }
    
    public function testCheckInputEndMonth2(): void
    {
       
        $this->assertEquals(FALSE, EstimateDeliveryDate::checkInputEndMonth("2020-1","2019-01"));
    }

    public function testCheckInputEndMonth3(): void
    {
       
        $this->assertEquals(TRUE, EstimateDeliveryDate::checkInputEndMonth("2020-1",""));
    }

    public function testCheckInputEndMonth4(): void
    {
       
        $this->assertEquals(FALSE, EstimateDeliveryDate::checkInputEndMonth("2020-1","2020-01"));
    }

    public function testGetStartDate(): void
    {
       
        $this->assertEquals("2020-01-01", EstimateDeliveryDate::getStartDate("2020-1"));
    }

    public function testGetEndDate(): void
    {
       
        $this->assertEquals("2020-02-29", EstimateDeliveryDate::getEndDate("2020-2"));
    }

     public function testStoreFromAPI(): void
    {
        EstimateDeliveryDate::storeFromAPI('2021');
        
        $this->assertContains(strval("2021-05-01"), EstimateDeliveryDate::$bankDays, "The mentioned bank day does not exist in array!");
    
        EstimateDeliveryDate::storeFromAPI('2020');
        
        $this->assertContains(strval("2020-05-01"), EstimateDeliveryDate::$bankDays, "The mentioned bank day does not exist in array!");
    }

    public function testCreateNonWorkingInterval(): void
    {
        EstimateDeliveryDate::createNonWorkingInterval('2021');
        
        $this->assertContains(strval("2021-07-24"), EstimateDeliveryDate::$bankDays, "The mentioned weekend day does not exist in array!");
    
        EstimateDeliveryDate::createNonWorkingInterval('2020');
        
        $this->assertContains(strval("2020-09-19"), EstimateDeliveryDate::$bankDays, "The mentioned weekend day does not exist in array!");
    }

    public function testCheckBankDays(): void
    {
        EstimateDeliveryDate::storeFromAPI('2021');
        EstimateDeliveryDate::createNonWorkingInterval('2021');
        $this->assertEquals(2, EstimateDeliveryDate::checkBankDays('2021-02-10', '2021-02-17'));
    
      
    }

    public function testEstimateBasedOnHistoricData1()
    {

        $historicalIntervalFromTable = array(9, 13, 7, 13, 8, 7, 6, 4, 5, 12, 8, 7, 14, 3, 4, 6, 5, 12, 7, 5, 12, 14, 14, 10 );
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'average',
            'noOfDaysAgo' => "", 
            'startMonth' => "2018-03", 
            'endMonth' => "2019-05"
        );                                   

        $this->assertEquals(9, EstimateDeliveryDate::estimateBasedOnHistoricData($input, $historicalIntervalFromTable));
       
    }

    public function testEstimateBasedOnHistoricData2()
    {

        $historicalIntervalFromTable2= array(9, 13, 7, 13, 8, 7, 6, 4, 5, 12, 8, 7, 14, 3, 4, 6, 5, 12, 7, 5, 12, 14, 14, 10 );
        $input = array(
            'zipCode' => 30116, 
            'orderDate' => date('Y-m-d', strtotime('m')),
            'meanOption' => 'max_values',
            'noOfDaysAgo' => "", 
            'startMonth' => "2018-03", 
            'endMonth' => "2019-05"
        );
        $this->assertEquals(7, EstimateDeliveryDate::estimateBasedOnHistoricData($input, $historicalIntervalFromTable2));

    }


    

    
}