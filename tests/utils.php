<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * @package org_routamc_positioning
 */
class org_routamc_positioning_tests_utils extends midcom_tests_testcase
{
    public function test_distance()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new org_routamc_positioning_spot(60.254558, 25.042828);
 
        // Helsinki-Vantaa airport (EFHK)
        $efhk = new org_routamc_positioning_spot(60.317222, 24.963333);

        // Midgard airport (FYMG)
        $fymg = new org_routamc_positioning_spot(-22.083332, 17.366667);
       
        // There are 8.2 kilometers between the airports
        $distance = org_routamc_positioning_utils::get_distance($efhf, $efhk);
        $this->assertEquals($distance, 8.2);
        
        // 8.2 kilometers is approximately 4.4 nautical miles
        $distance_nautical =  org_routamc_positioning_utils::get_distance($efhf, $efhk, 'N');
        $this->assertEquals($distance_nautical, 4.4);

        // There are 9181.6 kilometers from Helsinki to Midgard
        $distance_large = org_routamc_positioning_utils::get_distance($efhf, $fymg);
        $this->assertEquals($distance_large, 9181.6);
    }

    public function test_bearing()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new org_routamc_positioning_spot(60.254558, 25.042828);
 
        // Helsinki-Vantaa airport (EFHK)
        $efhk = new org_routamc_positioning_spot(60.317222, 24.963333);
        
        // Helsinki-Vantaa is in north of Helsinki-Malmi
        $bearing = org_routamc_positioning_utils::get_bearing($efhf, $efhk);
        $this->assertEquals($bearing, 'N');

        // Helsinki-Malmi is in south of Helsinki-Vantaa
        $bearing = org_routamc_positioning_utils::get_bearing($efhk, $efhf);
        $this->assertEquals($bearing, 'S');
    }

    public function test_coordinate_to_decimal()
    {
        // Helsinki-Malmi airport (EFHF)
        $latitude = org_routamc_positioning_utils::coordinate_to_decimal(60, 15, 16);
        $this->assertEquals(round($latitude, 2), round(60.254558, 2));
        $longitude = org_routamc_positioning_utils::coordinate_to_decimal(25, 2, 34);
        $this->assertEquals(round($longitude, 2), round(25.042828, 2));
    }
    
    public function test_decimal_to_coordinate()
    {
        // Midgard airport (FYMG)
        $latitude = org_routamc_positioning_utils::decimal_to_coordinate(-22.083332);
        $longitude = org_routamc_positioning_utils::decimal_to_coordinate(17.366667);

        $this->assertTrue(is_array($latitude));
        $this->assertTrue(is_array($longitude));
        
        $this->assertEquals($latitude['deg'], -22);
        $this->assertEquals($longitude['deg'], 17);
    }
    
    public function test_bounding_box_for_radius()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new org_routamc_positioning_spot(60.254558, 25.042828);
        
        // Get 20km bounding box
        $bbox = org_routamc_positioning_utils::get_bounding_box_for_radius($efhf, 20);
        
        $this->assertTrue(is_array($bbox));
        $this->assertEquals(count($bbox), 2);
        
        // Ensure the box limits are in right directions
        $this->assertEquals(org_routamc_positioning_utils::get_bearing($efhf, $bbox[0]), 'SW');
        $this->assertEquals(org_routamc_positioning_utils::get_bearing($efhf, $bbox[1]), 'NE');
        
        // Check that the distance to a corner is correct. 
        // Note: using 2D trigonometry on 3D globe so numbers are not exact
        $distance1 = org_routamc_positioning_utils::get_distance($bbox[0], $efhf);
        $this->assertEquals(round($distance1), round(sqrt(pow(20, 2) + pow(20, 2))));
    }
}
?>
