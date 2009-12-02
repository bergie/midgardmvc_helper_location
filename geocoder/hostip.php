<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * HostIP.info geocoding service, geocodes IP addresses
 *
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_geocoder_hostip implements midgardmvc_helper_location_geocoder
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
        
        $xml = @file_get_contents("http://api.hostip.info/?ip={$location['ip']}");
        if (!$xml)
        {
            throw new RuntimeException("HostIP did not return data");
        }
        
        // Load XML through SimpleXML, working around the namespacing issue from http://bugs.php.net/bug.php?id=48049
        $simplexml = simplexml_load_string(str_replace(':', '_', $xml));
        
        // Check that we got coordinates
        if (!isset($simplexml->gml_featureMember->Hostip->ipLocation->gml_pointProperty->gml_Point->gml_coordinates))
        {
            // TODO: Geocode based on city
            return null;
        }
        $coordinates = explode(',', (string) $simplexml->gml_featureMember->Hostip->ipLocation->gml_pointProperty->gml_Point->gml_coordinates);
        
        $spot = new midgardmvc_helper_location_spot((float) $coordinates[1], (float) $coordinates[0]);
        $spot->source = 'hostip';
        $spot->accuracy = 80;

        if (isset($simplexml->gml_featureMember->Hostip->countryAbbrev))
        {
            $spot->country = (string) $simplexml->gml_featureMember->Hostip->countryAbbrev;
            $spot->accuracy = 60;
        }
        
        if (isset($simplexml->gml_featureMember->Hostip->gml_name))
        {
            $spot->city = (string) $simplexml->gml_featureMember->Hostip->gml_name;
            $spot->accuracy = 30;
        }
        
        if (   isset($simplexml->gml_featureMember->Hostip->gml_name)
            && isset($simplexml->gml_featureMember->Hostip->countryName))
        {
            $spot->text = "{$simplexml->gml_featureMember->Hostip->gml_name}, " . ucfirst(strtolower((string) $simplexml->gml_featureMember->Hostip->countryName));
        }

        return $spot;
    }
}
