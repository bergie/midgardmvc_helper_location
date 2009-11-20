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
class org_routamc_positioning_tests_user extends midcom_tests_testcase
{
    public function test_set_anonymous()
    {
        $_SESSION = array();
        // Midgard airport (FYMG)
        $fymg = new org_routamc_positioning_spot(-22.083332, 17.366667);

        $this->assertTrue(org_routamc_positioning_user::set_location($fymg));
    }

    /**
     * @depends test_set_anonymous
     */
    public function test_get_anonymous()
    {
        // Midgard airport (FYMG)
        $fymg = new org_routamc_positioning_spot(-22.083332, 17.366667);

        $location = org_routamc_positioning_user::get_location();
        
        $this->assertTrue(is_a($location, 'org_routamc_positioning_spot'));
        
        $this->assertEquals($location->latitude, $fymg->latitude);
        $this->assertEquals($location->longitude, $fymg->longitude);
    }
}
?>
