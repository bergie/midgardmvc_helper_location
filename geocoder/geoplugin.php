<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * GeoPlugin geocoding service, geocodes IP addresses
 *
 * GeoPlugin (http://www.geoplugin.com) is a free-to-use IP-to-location geocoding service.
 * However, they request users of their service to provide a link to their site using
 * something like:
 *
 * <code>
 * <a href="http://www.geoplugin.com/" target="_new" title="geoPlugin for IP geolocation">Geolocation by geoPlugin</a>
 * </code>
 *
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_geocoder_geoplugin implements midgardmvc_helper_location_geocoder
{
    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param array $location Parameters to geocode with, conforms to XEP-0080
     * @return midgardmvc_helper_location_spot containing geocoded information
     */
    public function geocode(array $location)
    {
        if (!isset($location['ip']))
        {
            throw new InvalidArgumentException("No IP address provided");
        }
        
        // Check that we have a valid IP
        if (!filter_var($location['ip'], FILTER_VALIDATE_IP))
        {
            throw new InvalidArgumentException("Invalid IP address provided");
        }
        
        $json = @file_get_contents("http://www.geoplugin.net/json.gp?ip={$location['ip']}");
        if (!$json)
        {
            throw new RuntimeException("GeoPlugin did not return data");
        }
        
        $location = json_decode($json);
        $spot = new midgardmvc_helper_location_spot((float) $location->geoplugin_latitude, (float) $location->geoplugin_longitude);
        $spot->accuracy = 80;
        $spot->source = 'geoplugin';

        if (isset($location->geoplugin_countryCode))
        {
            $spot->country = $location->geoplugin_countryCode;
            $spot->accuracy = 60;
        }

        if (isset($location->geoplugin_city))
        {
            $spot->city = $location->geoplugin_city;
            $spot->accuracy = 30;
        }
        
        if (   isset($location->geoplugin_city)
            && isset($location->geoplugin_countryName))
        {
            $spot->text = "{$location->geoplugin_city}, {$location->geoplugin_countryName}";
        }

        return $spot;
    }
}
