<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_tests_geocoder_tiny extends midgardmvc_tests_testcase
{
    /**
     * Try geocoding Helsinki
     */
    public function test_helsinki()
    {
        $geocoder = new midgardmvc_helper_location_geocoder_tiny();     
        $data = array
        (
            'city' => 'Helsinki',
            'country' => 'Finland',
        );
        $spot = $geocoder->geocode($data);
        
        // Check that we got the correct type
        $this->assertTrue(is_a($spot, 'midgardmvc_helper_location_spot'));

        // Check that the type is near Helsinki
        $this->assertEquals((int) round($spot->latitude), 60);
        $this->assertEquals((int) round($spot->longitude), 25);
        
        // Check that we got a textual location
        $this->assertEquals($spot->text, 'Helsinki, Finland');
        
        // Check that accuracy is correctly set to "city"
        $this->assertEquals($spot->accuracy, 30);
        
        // Check that source is correct
        $this->assertEquals($spot->source, 'tinygeocoder');
    }

    /**
     * Try geocoding Museokatu, check city and country
     */
    public function test_museokatu()
    {
        $geocoder = new midgardmvc_helper_location_geocoder_tiny();     
        $data = array
        (
            'street' => 'Museokatu 35',
            'city' => 'Helsinki',
            'country' => 'Finland',
        );
        $spot = $geocoder->geocode($data);
        
        // Check that we got the correct type
        $this->assertTrue(is_a($spot, 'midgardmvc_helper_location_spot'));

        // Check that the spot is near the right place
        $this->assertEquals(round($spot->latitude), round(60.17541615751));
        $this->assertEquals(round($spot->longitude), round(24.919127225876));
        
        // Check that we got a textual location
        $this->assertEquals($spot->text, 'Museokatu 35, Helsinki, Finland');
        
        // Check that accuracy is correctly set to "city"
        $this->assertEquals($spot->accuracy, 30);
        
        // Check that source is correct
        $this->assertEquals($spot->source, 'tinygeocoder');
    }
 
    public function test_helsinki_reverse()
    {
        $museokatu = new midgardmvc_helper_location_spot(60.175416157517, 24.919127225876);
    
        $geocoder = new midgardmvc_helper_location_geocoder_tiny();
        $spot = $geocoder->reverse_geocode($museokatu);
        
        // Check that we got the correct type
        $this->assertTrue(is_a($spot, 'midgardmvc_helper_location_spot'));

        // Check that the type is near Helsinki
        $this->assertEquals((int) round($spot->latitude), 60);
        $this->assertEquals((int) round($spot->longitude), 25);
        
        // Check that we got a textual location
        $this->assertEquals($spot->text, 'Museigatan 40-46, 00100 Helsinki, Finland');

        // Check that accuracy is correctly set to "city"
        $this->assertEquals($spot->accuracy, 30);
        
        // Check that source is correct
        $this->assertEquals($spot->source, 'tinygeocoder');
    }
}
?>
