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
class midgardmvc_helper_location_tests_geocoder_geoplugin extends PHPUnit_FrameWork_TestCase
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
            'ip' => '84.20.132.117',
        );
        $spot = $geocoder->geocode($data);
        
        // Check that we got the correct type
        $this->assertTrue(is_a($spot, 'midgardmvc_helper_location_spot'));
        // Check that the type is near Finland
        $this->assertEquals(64, (int) round($spot->latitude));
        $this->assertEquals(26, (int) round($spot->longitude));
        
        // Check that we got city and country
        $this->assertEquals('FI', $spot->country);
        $this->assertEquals('', $spot->city);
        
        // Check that we got a textual location
        $this->assertEquals(', Finland', $spot->text);
        
        // Check that accuracy is correctly set to "city"
        $this->assertEquals(30, $spot->accuracy);
        
        // Check that source is correct
        $this->assertEquals('geoplugin', $spot->source);
    }
}
?>
