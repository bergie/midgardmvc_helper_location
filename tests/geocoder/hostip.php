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
class org_routamc_positioning_tests_geocoder_hostip extends midcom_tests_testcase
{
    /**
     * Try geocoding without IP, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_no_ip()
    {
        $geocoder = new org_routamc_positioning_geocoder_hostip();
        
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
        $geocoder = new org_routamc_positioning_geocoder_hostip();    
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
        $geocoder = new org_routamc_positioning_geocoder_hostip();     
        $data = array
        (
            'ip' => '83.150.122.98',
        );
        $spot = $geocoder->geocode($data);
        
        // Check that we got the correct type
        $this->assertTrue(is_a($spot, 'org_routamc_positioning_spot'));
        
        // Check that the type is near Helsinki
        $this->assertEquals((int) $spot->latitude, 60);
        $this->assertEquals((int) $spot->longitude, 25);
        
        // Check that we got city and country
        $this->assertEquals($spot->country, 'FI');
        $this->assertEquals($spot->city, 'Helsinki');

        // Check that we got a textual location
        $this->assertEquals($spot->text, 'Helsinki, Finland');

        // Check that accuracy is correctly set to "city"
        $this->assertEquals($spot->accuracy, 30);

        // Check that source is correct
        $this->assertEquals($spot->source, 'hostip');
    }
}
?>
