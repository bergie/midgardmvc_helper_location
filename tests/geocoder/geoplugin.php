<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once(dirname(__FILE__) . '/../../../midgardmvc_core/tests/testcase.php');

/**
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_tests_geocoder_geoplugin extends midgardmvc_core_tests_testcase
{
    /**
     * Try geocoding without IP, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_no_ip()
    {
        $geocoder = new midgardmvc_helper_location_geocoder_geoplugin();
        
        $data = array
        (
            'city' => 'Helsinki',
            'country' => 'FI',
        );
        
        $geocoder->geocode($data);
    }

    /**
     * Try geocoding invalid IP, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_invalid_ip()
    {
        $geocoder = new midgardmvc_helper_location_geocoder_geoplugin();    
        $data = array
        (
            'ip' => '666.666.666.666',
        );
        $geocoder->geocode($data);
    }

    /**
     * Try geocoding a Finnish IP, check city and country
     */
    public function test_finnish_ip()
    {
        $geocoder = new midgardmvc_helper_location_geocoder_geoplugin();     
        $data = array
        (
            'ip' => '83.150.122.98',
        );
        $spot = $geocoder->geocode($data);
        
        // Check that we got the correct type
        $this->assertTrue(is_a($spot, 'midgardmvc_helper_location_spot'));

        // Check that the type is near Helsinki
        $this->assertEquals((int) round($spot->latitude), 60);
        $this->assertEquals((int) round($spot->longitude), 25);
        
        // Check that we got city and country
        $this->assertEquals($spot->country, 'FI');
        $this->assertEquals($spot->city, 'Helsinki');
        
        // Check that we got a textual location
        $this->assertEquals($spot->text, 'Helsinki, Finland');
        
        // Check that accuracy is correctly set to "city"
        $this->assertEquals($spot->accuracy, 30);
        
        // Check that source is correct
        $this->assertEquals($spot->source, 'geoplugin');
    }
}
?>
