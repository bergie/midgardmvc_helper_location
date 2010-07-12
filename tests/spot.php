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
class midgardmvc_helper_location_tests_spot extends PHPUnit_FrameWork_TestCase
{
    public function test_instantiate_from_coordinates()
    {
        // Midgard airport (FYMG)
        $spot = new midgardmvc_helper_location_spot(-22.083332, 17.366667);
        
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
        $spot = new midgardmvc_helper_location_spot(-22.083332);
    } 

    public function test_instantiate_from_string()
    {
        $spot = new midgardmvc_helper_location_spot('low earth orbit');
        
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
        $spot = new midgardmvc_helper_location_spot("foo", "bar");
    }

    /**
     * Try instantiating spot from int, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_ints()
    {
        $spot = new midgardmvc_helper_location_spot(1, 2);
    }

    /**
     * Try instantiating spot from implausible coordinates, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_implausible_latitude()
    {
        $spot = new midgardmvc_helper_location_spot(95.2, 17.366667);
    }

    /**
     * Try instantiating spot from implausible coordinates, should throw an exception
     * 
     * @expectedException InvalidArgumentException
     */
    public function test_instantiate_from_implausible_longitude()
    {
        $spot = new midgardmvc_helper_location_spot(-22.083332, -185.6);
    }

    public function test_instantiate_from_location()
    {
        if (!class_exists('midgardmvc_helper_location_location'))
        {
            $this->markTestSkipped('Midgard location schema is not available');
        }
        // Midgard airport (FYMG)
        $location = new midgardmvc_helper_location_location();
        $location->latitude = -22.083332;
        $location->longitude = 17.366667;
        
        $spot = new midgardmvc_helper_location_spot($location);
        
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
        $original = new midgardmvc_helper_location_spot(-22.083332, 17.366667);
        
        $spot = new midgardmvc_helper_location_spot($original);
    }

    public function test_distance()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new midgardmvc_helper_location_spot(60.254558, 25.042828);
 
        // Helsinki-Vantaa airport (EFHK)
        $efhk = new midgardmvc_helper_location_spot(60.317222, 24.963333);

        // Midgard airport (FYMG)
        $fymg = new midgardmvc_helper_location_spot(-22.083332, 17.366667);
       
        // There are 8.2 kilometers between the airports
        $distance = $efhf->distance_to($efhk);
        $this->assertEquals($distance, 8.2);
        
        // 8.2 kilometers is approximately 4.4 nautical miles
        $distance_nautical =  $efhf->distance_to($efhk, 'N');
        $this->assertEquals($distance_nautical, 4.4);

        // There are 9181.6 kilometers from Helsinki to Midgard
        $distance_large = $efhf->distance_to($fymg);
        $this->assertEquals($distance_large, 9181.6);
        $distance_large = $efhf->distance_to($fymg, 'N');
        $this->assertEquals($distance_large, 4954.4);
    }

    public function test_bearing()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new midgardmvc_helper_location_spot(60.254558, 25.042828);
 
        // Helsinki-Vantaa airport (EFHK)
        $efhk = new midgardmvc_helper_location_spot(60.317222, 24.963333);
        
        // Helsinki-Vantaa is in north of Helsinki-Malmi
        $bearing = $efhf->bearing_to($efhk);
        $this->assertEquals($bearing, 329);

        // Helsinki-Malmi is in south of Helsinki-Vantaa
        $bearing = $efhk->bearing_to($efhf);
        $this->assertEquals($bearing, 149);

        // Test bearing within same location
        $bearing = $efhf->bearing_to($efhf);
        $this->assertEquals($bearing, null);

        // Midgard airport (FYMG)
        $fymg = new midgardmvc_helper_location_spot(-22.083332, 17.366667);
        $bearing = $efhf->bearing_to($fymg);
        $this->assertEquals($bearing, 187);
    }

    public function test_direction()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new midgardmvc_helper_location_spot(60.254558, 25.042828);
 
        // Helsinki-Vantaa airport (EFHK)
        $efhk = new midgardmvc_helper_location_spot(60.317222, 24.963333);
        
        // Helsinki-Vantaa is in northwest of Helsinki-Malmi
        $bearing = $efhf->direction_to($efhk);
        $this->assertEquals($bearing, 'NW');

        // Helsinki-Malmi is in southeast of Helsinki-Vantaa
        $bearing = $efhk->direction_to($efhf);
        $this->assertEquals($bearing, 'SE');

        // Midgard airport (FYMG)
        $fymg = new midgardmvc_helper_location_spot(-22.083332, 17.366667);
        $bearing = $efhf->direction_to($fymg);
        $this->assertEquals($bearing, 'S');
    }

    public function test_bounding_box()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new midgardmvc_helper_location_spot(60.254558, 25.042828);
        
        // Get 20km bounding box
        $bbox = $efhf->get_bounding_box(20);
        
        $this->assertTrue(is_array($bbox));
        $this->assertEquals(count($bbox), 2);
        
        // Ensure the box limits are in right directions
        $this->assertEquals($efhf->direction_to($bbox[0]), 'SW');
        $this->assertEquals($efhf->direction_to($bbox[1]), 'NE');
        
        // Check that the distance to a corner is correct. 
        // Note: using 2D trigonometry on 3D globe so numbers are not exact
        $distance1 = $bbox[0]->distance_to($efhf);
        $this->assertEquals(round($distance1), round(sqrt(pow(20, 2) + pow(20, 2))));
    }

    public function test_toString()
    {
        // Helsinki-Malmi airport (EFHF)
        $efhf = new midgardmvc_helper_location_spot(60.254558, 25.042828);

        $coordinates = $efhf->__toString();

        $this->assertEquals($coordinates, 'N 60° 15.273, E 25° 2.570');
    }
}
?>
