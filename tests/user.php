<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_tests_user extends midgardmvc_core_tests_testcase
{
    public function test_set_anonymous()
    {
        $this->markTestSkipped();
        $_SESSION = array();
        // Midgard airport (FYMG)
        $fymg = new midgardmvc_helper_location_spot(-22.083332, 17.366667);

        $this->assertTrue(midgardmvc_helper_location_user::set_location($fymg));
    }

    /**
     * @depends test_set_anonymous
     */
    public function test_get_anonymous()
    {
        // Midgard airport (FYMG)
        $fymg = new midgardmvc_helper_location_spot(-22.083332, 17.366667);

        $location = midgardmvc_helper_location_user::get_location();
        
        $this->assertTrue(is_a($location, 'midgardmvc_helper_location_spot'));
        
        $this->assertEquals($location->latitude, $fymg->latitude);
        $this->assertEquals($location->longitude, $fymg->longitude);
    }
}
?>
