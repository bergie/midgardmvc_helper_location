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
class org_routamc_positioning_tests_spot extends midgardmvc_tests_testcase
{
    public function test_instantiate_from_coordinates()
    {
        // Midgard airport (FYMG)
        $spot = new org_routamc_positioning_spot(-22.083332, 17.366667);
        
        $this->assertEquals($spot->latitude, -22.083332);
        $this->assertEquals($spot->longitude, 17.366667);
        $this->assertEquals($spot->when, null);
    }

    /**
     * Try instantiating spot from just one coordinate, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_one_coordinate()
    {
        $spot = new org_routamc_positioning_spot(-22.083332);
    } 

    public function test_instantiate_from_string()
    {
        $spot = new org_routamc_positioning_spot('low earth orbit');
        
        $this->assertEquals($spot->text, 'low earth orbit');
        $this->assertEquals($spot->accuracy, 80);
    }

    /**
     * Try instantiating spot from string, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_strings()
    {
        $spot = new org_routamc_positioning_spot("foo", "bar");
    }

    /**
     * Try instantiating spot from int, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_ints()
    {
        $spot = new org_routamc_positioning_spot(1, 2);
    }

    /**
     * Try instantiating spot from implausible coordinates, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_implausible_latitude()
    {
        $spot = new org_routamc_positioning_spot(95.2, 17.366667);
    }

    /**
     * Try instantiating spot from implausible coordinates, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_implausible_longitude()
    {
        $spot = new org_routamc_positioning_spot(-22.083332, -185.6);
    }

    public function test_instantiate_from_location()
    {
        // Midgard airport (FYMG)
        $location = new org_routamc_positioning_location();
        $location->latitude = -22.083332;
        $location->longitude = 17.366667;
        
        $spot = new org_routamc_positioning_spot($location);
        
        $this->assertEquals($spot->latitude, $location->latitude);
        $this->assertEquals($spot->longitude, $location->longitude);
        $this->assertEquals($spot->when, $location->metadata->created);
    }

    /**
     * Try instantiating spot from non-MgdSchema object, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_invalid_class()
    {
        // Midgard airport (FYMG)
        $original = new org_routamc_positioning_spot(-22.083332, 17.366667);
        
        $spot = new org_routamc_positioning_spot($original);
    }
}
?>
