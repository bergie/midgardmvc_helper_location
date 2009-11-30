<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * GeoPlugin geocoding service
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_geocoder_geonames implements org_routamc_positioning_geocoder
{
    /**
     * Geocode a city name using the GeoNames database
     *
     * @param array $location Parameters to geocode with, conforms to XEP-0080
     * @return org_routamc_positioning_spot containing geocoded information
     */
    public function geocode(array $location)
    {
        // TODO: Make these configurable
        $parameters = array
        (
            'radius' => null,
            'maxRows' => 1,
            'style' => 'FULL',
        );

        if (   !isset($location['postalcode'])
            && !isset($location['city']))
        {
            throw new InvalidArgumentException("Postal code or city required for geocoding");
        }

        $params = array();
        if (isset($location['postalcode']))
        {
            $params[] = 'postalcode=' . urlencode($location['postalcode']);
        }
        if (isset($location['city']))
        {
            $params[] = 'placename=' . urlencode($location['city']);
        }
        if (isset($location['country']))
        {
            $params[] = 'country=' . urlencode($location['country']);
        }

        foreach ($parameters as $key => $value)
        {
            if (! is_null($value))
            {
                $params[] = "{$key}=" . urlencode($value);
            }
        }
        
        $response = @file_get_contents('http://ws.geonames.org/postalCodeSearch?' . implode('&', $params));
        if (!$response)
        {
            throw new RuntimeException("GeoNames did not return data");
        }

        $simplexml = simplexml_load_string($response);
        if (   !isset($simplexml->code)
            || count($simplexml->code) == 0)
        {
            throw new midgardmvc_exception_notfound('City not found');
        }
        
        $entry = $simplexml->code[0];

        // Prepare the spot        
        $spot = new org_routamc_positioning_spot((float) $entry->lat, (float) $entry->lng);
        $spot->accuracy = 30;
        $spot->source = 'geonames';
        
        $spot->city = (string) $entry->name;
        $spot->country = (string) $entry->countryCode;
        $spot->postalcode = (string) $entry->postalcode;

        // TODO: Include country name
        $spot->text = (string) $entry->name;
        
        return $spot;
    }

    /**
     * Reverse geocode using the GeoNames service.
     *
     * @param org_routamc_positioning_spot $spot Spot to geocode
     * @return org_routamc_positioning_spot containing geocoded information
     */
    public function reverse_geocode(org_routamc_positioning_spot $location)
    {
        $parameters = array
        (
            'radius' => 10,
            'maxRows' => 20,
            'style' => 'FULL',
        );

        if (   !$location->latitude
            && !$location->longitude)
        {
            throw new InvalidArgumentException('The spot to reverse geocode must contain coordinates');
        }
        
        $params = array();
        
        $params[] = 'lat=' . urlencode($location->latitude);
        $params[] = 'lng=' . urlencode($location->longitude);
        
        foreach ($parameters as $key => $value)
        {
            if (!is_null($value))
            {
                $params[] = "{$key}=" . urlencode($value);
            }
        }
        
        $response = @file_get_contents('http://ws.geonames.org/findNearbyPlaceName?' . implode('&', $params));
        if (!$response)
        {
            throw new RuntimeException("GeoNames did not return data");
        }
        
        $simplexml = simplexml_load_string($response);
        if (   !isset($simplexml->geoname)
            || count($simplexml->geoname) == 0)
        {
            throw new midgardmvc_exception_notfound('City not found');
        }
            
        $entry = $simplexml->geoname[0];
        $entry_location = new org_routamc_positioning_spot((float) $entry->lat, (float) $entry->lng);
        $entry_location->accuracy = 30;
        $entry_location->source = 'geonames';
        
        $entry_location->city = (string) $entry->name;
        $entry_location->country = (string) $entry->countryCode;
        $entry_location->postalcode = (string) $entry->postalcode;
        
        // TODO: Include country name
        $entry_location->text = (string) $entry->name;

        return $entry_location;           
    }
}
