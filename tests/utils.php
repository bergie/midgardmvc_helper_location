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
        
        // There are 8.2 kilometers between the airports
        $distance = org_routamc_positioning_utils::get_distance($efhf, $efhk);
        $this->assertEquals($distance, 8.2);
        
        // 8.2 kilometers is approximately 4.4 nautical miles
        $distance_nautical =  org_routamc_positioning_utils::get_distance($efhf, $efhk, 'N');
        $this->assertEquals($distance_nautical, 4.4);
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
}
?>
