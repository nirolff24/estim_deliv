<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
$file_root_path = $_SERVER["DOCUMENT_ROOT"] . "./";
require $file_root_path . "EstimateDeliveryDate.php";

final class EstimateDeliveryDateTest extends TestCase
{
    public function testReadDateInterval1(): void
    {
        $date_range2 = array(
            'endDate'=>'2021-01-29',
            'startDate'=>'2021-01-19'
        );
        $this->assertEquals($date_range2, EstimateDeliveryDate::readDateInterval("10","",""));
    }

    public function testReadDateInterval2(): void
    {
        $date_range2 = array(
            'endDate'=>'2020-10-31',
            'startDate'=>'2020-10-01'
        );
        $this->assertEquals($date_range2, EstimateDeliveryDate::readDateInterval("","October-2020",""));
    }

    public function testReadDateInterval3(): void
    {
        $date_range2 = array(
            'endDate'=>'2021-01-29',
            'startDate'=>'2021-01-01'
        );
        $this->assertEquals($date_range2, EstimateDeliveryDate::readDateInterval("","January-2021",""));
    }

    
}