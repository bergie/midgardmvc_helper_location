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
class org_routamc_positioning_tests_spot extends midcom_tests_testcase
{
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
