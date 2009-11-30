<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Controller for getting and updating user's location
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_controllers_userlocation
{
    public function __construct(midgardmvc_core_component_interface $instance)
    {
        $this->configuration = $instance->configuration;
    }

    /**
     * Update user's location
     */
    public function post_location(array $args)
    {
        if (   isset($_POST['latitude'])
            && isset($_POST['longitude']))
        {
            $spot = new org_routamc_positioning_spot((float) $_POST['latitude'], (float) $_POST['longitude']);
            if (isset($_POST['text']))
            {
                // User has provided a textual location
                $spot->text = $_POST['text'];
            }
            else
            {
                try
                {
                    // Get textual location by reverse geocoding
                    $geocoder = new org_routamc_positioning_geocoder_geonames();
                    $city = $geocoder->reverse_geocode($spot);
                    if ($city->text)
                    {
                        $spot->text = $city->text;
                    }
                }
                catch (Exception $e)
                {
                    // Ignore silently
                }
            }
        }
        elseif (isset($_POST['text']))
        {
            $spot = new org_routamc_positioning_spot($_POST['text']);
        }
        else
        {
            throw new InvalidArgumentException("Expected latitude and longitude, or text not found");
        }
        
        if (isset($_POST['accuracy']))
        {
            // W3C accuracy is in meters, convert to our approximates
            if ($_POST['accuracy'] < 30)
            {
                // Exact enough
                $spot->accuracy = 10;
            }
            elseif ($_POST['accuracy'] < 400)
            {
                // Postal code area
                $spot->accuracy = 20;
            }
            elseif ($_POST['accuracy'] < 5000)
            {
                // City
                $spot->accuracy = 30;
            }
            else
            {
                // Fall back to "state level"
                $spot->accuracy = 50;
            }  
        }
        
        $spot->source = 'browser';
        
        if (!org_routamc_positioning_user::set_location($spot))
        {
            throw new midgardmvc_exception_httperror("Failed to store location");
        }
        
        midgardmvc_core::get_instance()->log("postlocation", "Location stored", 'debug');
        
        $this->get_location($args);
    }

    /**
     * Read user's location
     */
    public function get_location(array $args)
    {
        $this->data = org_routamc_positioning_user::get_location();
    }
}
?>
