<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Tiny Geocoder geocoding service
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_geocoder_tiny implements org_routamc_positioning_geocoder
{
    /**
     * Geocode a city name using the Tiny Geocoder service
     *
     * @param array $location Parameters to geocode with
     * @return org_routamc_positioning_spot containing geocoded information
     */
    public function geocode(array $location)
    {
        $params = urlencode(implode(',', $location));
        $response = @file_get_contents('http://tinygeocoder.com/create-api.php?q=' . $params);
        if (!$response)
        {
            throw new RuntimeException("Tiny Geocoder did not return data");
        }
        
        $response_parts = explode(',', $response);
        if (count($response_parts) != 2)
        {
            throw new RunTimeException("Tiny Geocoder returned incorrect data");
        }

        // Prepare the spot        
        $spot = new org_routamc_positioning_spot((float) $response_parts[0], (float) $response_parts[1]);
        $spot->accuracy = 30;
        $spot->source = 'tinygeocoder';

        // TODO: Include country name
        $spot->text = implode(', ', $location);
        
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
        $response = @file_get_contents("http://tinygeocoder.com/create-api.php?g={$location->latitude},{$location->longitude}");
        if (!$response)
        {
            throw new RuntimeException("Tiny Geocoder did not return data");
        }

        // Prepare the spot        
        $spot = new org_routamc_positioning_spot($response);
        $spot->latitude = $location->latitude;
        $spot->longitude = $location->longitude;
        $spot->accuracy = 30;
        $spot->source = 'tinygeocoder';
      
        return $spot;
    }
}
