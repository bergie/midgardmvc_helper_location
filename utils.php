<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Position handling utils using static methods
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_utils
{
    /**
     * Get distance between to positions in kilometers
     *
     * Code from http://www.corecoding.com/getfile.php?file=25
     */
    static function get_distance(org_routamc_positioning_spot $from, org_routamc_positioning_spot $to, $unit = 'K', $round = true)
    {
        $theta = $from->longitude - $to->longitude;
        $dist = sin(deg2rad($from->latitude)) * sin(deg2rad($to->latitude)) + cos(deg2rad($from->latitude)) * cos(deg2rad($to->latitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $dist = $dist * 60 * 1.1515;

        if ($unit == "K")
        {
            $dist *= 1.609344;
        }
        else if ($unit == "N")
        {
            $dist *= 0.8684;
        }

        if ($round)
        {
            $dist = round($dist, 1);
        }
        return $dist;
    }

   /**
     * Get bearing from position to another
     *
     * Code from http://www.corecoding.com/getfile.php?file=25
     */
    static function get_bearing(org_routamc_positioning_spot $from, org_routamc_positioning_spot $to)
    {
        if (round($from->longitude, 1) == round($to->longitude, 1))
        {
            if ($from->latitude < $to->latitude)
            {
                $bearing = 0;
            }
            else
            {
                $bearing = 180;
            }
        }
        else
        {
            $dist = org_routamc_positioning_utils::get_distance($from, $to, 'N');
            $arad = acos((sin(deg2rad($to->latitude)) - sin(deg2rad($from->latitude)) * cos(deg2rad($dist / 60))) / (sin(deg2rad($dist / 60)) * cos(deg2rad($from->latitude))));
            $bearing = $arad * 180 / pi();
            if (sin(deg2rad($to->longitude - $from->longitude)) < 0)
            {
                $bearing = 360 - $bearing;
            }
        }

        $dirs = array('N', 'E', 'S', 'W');

        $rounded = round($bearing / 22.5) % 16;
        if (($rounded % 4) == 0)
        {
            $dir = $dirs[$rounded / 4];
        }
        else
        {
            $dir = $dirs[2 * floor(((floor($rounded / 4) + 1) % 4) / 2)];
            $dir .= $dirs[1 + 2 * floor($rounded / 8)];
        }

        return $dir;
    }

    /**
     * Converts DMS ( Degrees / minutes / seconds ) to decimal format longitude / latitude
     *
     * Code from http://www.web-max.ca/PHP/misc_6.php
     */
    static function coordinate_to_decimal($deg, $min, $sec)
    {
        return $deg+((($min*60)+($sec))/3600);
    }

    /**
     * Converts decimal longitude / latitude to DMS ( Degrees / minutes / seconds )
     *
     * Code from http://www.web-max.ca/PHP/misc_6.php
     */
    static function decimal_to_coordinate($dec)
    {
        // This is the piece of code which may appear to
        // be inefficient, but to avoid issues with floating
        // point math we extract the integer part and the float
        // part by using a string function.
        $vars = explode('.', $dec);
        $deg = $vars[0];
        $tempma = "0.{$vars[1]}";
        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = $tempma - ($min*60);
        $coordinate = array
        (
            'deg' => $deg,
            'min' => $min,
            'sec' => $sec
        );
        return $coordinate;
    }

    /**
     * Pretty-print a coordinate value (latitude or longitude)
     *
     * Code from http://en.wikipedia.org/wiki/Geographic_coordinate_conversion
     *
     * @return string
     */
    static function pretty_print_coordinate($coordinate)
    {
        return sprintf("%0.0f° %2.3f",
                 floor(abs($coordinate)),
                 60*(abs($coordinate)-floor(abs($coordinate)))
        );
    }

    /**
     * Pretty-print a full coordinate (longitude and latitude)
     *
     * Code from http://en.wikipedia.org/wiki/Geographic_coordinate_conversion
     *
     * @return string
     */
    static function pretty_print_coordinates(org_routamc_positioning_spot $spot)
    {
        return sprintf("%s %s, %s %s",
                 ($spot->latitude > 0) ? 'N': 'S',  org_routamc_positioning_utils::pretty_print_coordinate($spot->latitude),
                 ($spot->longitude > 0) ? 'E': 'W', org_routamc_positioning_utils::pretty_print_coordinate($spot->longitude)
        );
    }

    /**
     * Pretty print a position mapping either to a city or cleaned coordinates
     *
     * @return string
     */
    static function pretty_print_location(org_routamc_positioning_spot $spot)
    {
        $closest = org_routamc_positioning_utils::get_closest('org_routamc_positioning_city', $spot, 1);
        $city_string = org_routamc_positioning_utils::pretty_print_coordinates($spot);
        foreach ($closest as $city)
        {
            $city_spot = new org_routamc_positioning_spot($city);
            $city_distance = round(org_routamc_positioning_utils::get_distance($spot, $city_spot));
            if ($city_distance <= 4)
            {
                $city_string = "{$city->city}, {$city->country}";
            }
            else
            {
                $bearing = org_routamc_positioning_utils::get_bearing($city_spot, $spot);
                $city_string = sprintf($_MIDCOM->i18n->get_string('%skm %s of %s', 'org_routamc_positioning'), $city_distance, $bearing, "{$city->city}, {$city->country}");
            }
        }
        return $city_string;
    }

    /**
     * Pretty print a position mapping Microformatted city name or other label
     *
     * @return string
     */
    static function microformat_location(org_routamc_positioning_spot $spot)
    {
        $closest = org_routamc_positioning_utils::get_closest('org_routamc_positioning_city', $spot, 1);

        $latitude_string = org_routamc_positioning_utils::pretty_print_coordinate($spot->latitude);
        $latitude_string .= ($spot->latitude > 0) ? ' N' : ' S';
        $longitude_string = org_routamc_positioning_utils::pretty_print_coordinate($spot->longitude);
        $longitude_string .= ($spot->longitude > 0) ? ' E' : ' W';

        if (count($closest) == 0)
        {
            // No city found, generate only geo microformat

            $coordinates_string  = "<span class=\"geo\">";
            $coordinates_string .= "<abbr class=\"latitude\" title=\"{$spot->latitude}\">{$latitude_string}</abbr> ";
            $coordinates_string .= "<abbr class=\"longitude\" title=\"{$spot->longitude}\">{$longitude_string}</abbr>";
            $coordinates_string .= "</span>";

            return $coordinates_string;
        }

        foreach ($closest as $city)
        {
            // City found, combine it and geo

            $city_string  = "<span class=\"geo adr\">";
            $city_string .= "<abbr class=\"latitude\" title=\"{$spot->latitude}\">{$latitude_string}</abbr> ";
            $city_string .= "<abbr class=\"longitude\" title=\"{$spot->longitude}\">{$longitude_string}</abbr> ";

            $city_spot = new org_routamc_positioning_spot($city);

            $city_distance = round(org_routamc_positioning_utils::get_distance($spot, $city_spot));

            $city_label  = "<span class=\"locality\">{$city->city}</span>, ";
            $city_label .= "<span class=\"country-name\">{$city->country}</span>";

            if ($city_distance <= 4)
            {
                $city_string .= $city_label;
            }
            else
            {
                $bearing = org_routamc_positioning_utils::get_bearing($city_spot, $spot);
                $city_string .= sprintf($_MIDCOM->i18n->get_string('%skm %s of %s', 'org_routamc_positioning'), $city_distance, $bearing, $city_label);
            }

            $city_string .= "</span>";
        }
        return $city_string;
    }

    /**
     * Figure out which class to use for positioning
     * @param string $class MidCOM class name
     * @param string $classname
     */
    static function get_positioning_class($class)
    {
        // See what kind of object we're querying for
        switch ($class)
        {
            case 'org_routamc_positioning_log':
            case 'org_routamc_positioning_city':
            case 'org_routamc_positioning_aerodrome':
                // Real position entry, query it directly
                $classname = $class;
                break;
            default:
                // Non-positioning MidCOM DBA object, query it through location cache
                $classname = 'org_routamc_positioning_location';
                break;
        }
        return $classname;
    }

    /**
     * Get closest items
     *
     * @param string $class MidCOM DBA class to query
     * @param org_routamc_positioning_spot $spot Center position
     * @param integer $limit How many results to return
     * @return Array Array of MidCOM DBA objects sorted by proximity
     */
    static function get_closest($class, org_routamc_positioning_spot $spot, $limit, $modifier = 0.15)
    {
        $classname = org_routamc_positioning_utils::get_positioning_class($class);
        if ($classname != $class)
        {
            $direct = false;
        }
        else
        {
            $direct = true;
        }
        $qb =  new midgard_query_builder($classname);

        if (!$direct)
        {
            // We're querying a regular DBA object through a location object
            $qb->add_constraint('parentclass', '=', $class);
        }

        static $rounds = 0;
        $rounds++;

        $from['latitude'] = $spot->latitude + $modifier;
        if ($from['latitude'] > 90)
        {
            $from['latitude'] = 90;
        }

        $from['longitude'] = $spot->longitude - $modifier;
        if ($from['longitude'] < -180)
        {
            $from['longitude'] = -180;
        }

        $to['latitude'] = $spot->latitude - $modifier;
        if ($to['latitude'] < -90)
        {
            $to['latitude'] = -90;
        }

        $to['longitude'] = $spot->longitude + $modifier;
        if ($to['longitude'] > 180)
        {
            $to['longitude'] = 180;
        }

        if (!isset($current_locale))
        {
            $current_locale = setlocale(LC_NUMERIC, '0');
            setlocale(LC_NUMERIC, 'C');
        }

        $qb->begin_group('AND');
        $qb->add_constraint('latitude', '<', (float) $from['latitude']);
        $qb->add_constraint('latitude', '>', (float) $to['latitude']);
        $qb->end_group();
        $qb->begin_group('AND');
        $qb->add_constraint('longitude', '>', (float) $from['longitude']);
        $qb->add_constraint('longitude', '<', (float) $to['longitude']);
        $qb->end_group();
        $result_count = $qb->count();
        //echo "<br />Round {$rounds}, lat1 {$from['latitude']} lon1 {$from['longitude']}, lat2 {$to['latitude']} lon2 {$to['longitude']}: {$result_count} results\n";

        if ($result_count < $limit)
        {
            if (   $from['latitude'] == 90
                && $from['longitude'] == -180
                && $to['latitude'] == -90
                && $to['longitude'] == 180)
            {
                // We've queried the entire globe so we return whatever we got
                $results = $qb->execute();
                $closest = Array();
                foreach ($results as $result)
                {
                    $result_spot = new org_routamc_positioning_spot($result);

                    $distance = sprintf("%05d", round(org_routamc_positioning_utils::get_distance($spot, $result_spot)));

                    if (!$direct)
                    {
                        // Instantiate the real object as the result
                        $result = new $class($result->parent);
                        $result->spot = $result_spot;
                        $result->latitude = $result_spot->latitude;
                        $result->longitude = $result_spot->longitude;
                    }

                    $closest[$distance . $result->guid] = $result;
                }
                ksort($closest);
                reset($closest);
                return $closest;
            }

            $modifier = $modifier * 1.05;
            setlocale(LC_NUMERIC, $current_locale);
            return org_routamc_positioning_utils::get_closest($class, $spot, $limit, $modifier);
        }

        $results = $qb->execute();
        $closest = array();
        foreach ($results as $result)
        {
            $result_spot = new org_routamc_positioning_spot($result);
            $distance = sprintf("%05d", round(org_routamc_positioning_utils::get_distance($spot, $result_spot)));

            if (!$direct)
            {
                // Instantiate the real object as the result
                $result = new $class($result->parent);
                $result->spot = $result_spot;
                $result->latitude = $result_spot->latitude;
                $result->longitude = $result_spot->longitude;
            }

            $closest[$distance . $result->guid] = $result;
        }

        ksort($closest);
        reset($closest);
        while (count($closest) > $limit)
        {
            array_pop($closest);
        }
        setlocale(LC_NUMERIC, $current_locale);
        return $closest;
    }
}
?>