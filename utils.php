<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Position handling utils using static methods
 *
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_utils
{
    /**
     * Get distance between to positions in kilometers
     *
     * Code from http://www.corecoding.com/getfile.php?file=25
     */
    static function get_distance(midgardmvc_helper_location_spot $from, midgardmvc_helper_location_spot $to, $unit = 'K', $round = true)
    {
        return $from->distance_to($to, $unit, $round);
    }

   /**
     * Get bearing from position to another
     *
     * Code from http://www.corecoding.com/getfile.php?file=25
     */
    static function get_bearing(midgardmvc_helper_location_spot $from, midgardmvc_helper_location_spot $to)
    {
        return $from->direction_to($to);
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
    static function pretty_print_coordinates(midgardmvc_helper_location_spot $spot)
    {
        return sprintf("%s %s, %s %s",
                 ($spot->latitude > 0) ? 'N': 'S',  midgardmvc_helper_location_utils::pretty_print_coordinate($spot->latitude),
                 ($spot->longitude > 0) ? 'E': 'W', midgardmvc_helper_location_utils::pretty_print_coordinate($spot->longitude)
        );
    }

    /**
     * Pretty print a position mapping either to a city or cleaned coordinates
     *
     * @return string
     */
    static function pretty_print_location(midgardmvc_helper_location_spot $spot)
    {
        $closest = midgardmvc_helper_location_utils::get_closest('midgardmvc_helper_location_city', $spot, 1);
        $city_string = midgardmvc_helper_location_utils::pretty_print_coordinates($spot);
        foreach ($closest as $city)
        {
            $city_spot = new midgardmvc_helper_location_spot($city);
            $city_distance = round(midgardmvc_helper_location_utils::get_distance($spot, $city_spot));
            if ($city_distance <= 4)
            {
                $city_string = "{$city->city}, {$city->country}";
            }
            else
            {
                $bearing = midgardmvc_helper_location_utils::get_bearing($city_spot, $spot);
                $city_string = sprintf(midgardmvc_core::get_instance()->i18n->get_string('%skm %s of %s', 'midgardmvc_helper_location'), $city_distance, $bearing, "{$city->city}, {$city->country}");
            }
        }
        return $city_string;
    }

    /**
     * Pretty print a position mapping Microformatted city name or other label
     *
     * @return string
     */
    static function microformat_location(midgardmvc_helper_location_spot $spot)
    {
        $closest = midgardmvc_helper_location_utils::get_closest('midgardmvc_helper_location_city', $spot, 1);

        $latitude_string = midgardmvc_helper_location_utils::pretty_print_coordinate($spot->latitude);
        $latitude_string .= ($spot->latitude > 0) ? ' N' : ' S';
        $longitude_string = midgardmvc_helper_location_utils::pretty_print_coordinate($spot->longitude);
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

            $city_spot = new midgardmvc_helper_location_spot($city);

            $city_distance = round(midgardmvc_helper_location_utils::get_distance($spot, $city_spot));

            $city_label  = "<span class=\"locality\">{$city->city}</span>, ";
            $city_label .= "<span class=\"country-name\">{$city->country}</span>";

            if ($city_distance <= 4)
            {
                $city_string .= $city_label;
            }
            else
            {
                $bearing = midgardmvc_helper_location_utils::get_bearing($city_spot, $spot);
                $city_string .= sprintf(midgardmvc_core::get_instance()->i18n->get_string('%skm %s of %s', 'midgardmvc_helper_location'), $city_distance, $bearing, $city_label);
            }

            $city_string .= "</span>";
        }
        return $city_string;
    }
    
    /**
     * Earth radius at a given latitude, according to the WGS-84 ellipsoid
     *
     * @see http://stackoverflow.com/questions/238260/how-to-calculate-the-bounding-box-for-a-given-lat-lng-location
     */
    static function get_earth_radius_at(midgardmvc_helper_location_spot $spot)
    {
        // Semi-axes of WGS-84 geoidal reference
        $WGS84_a = 6378137.0; // Major semiaxis [m]
        $WGS84_b = 6356752.3; // Minor semiaxis [m]

        // http://en.wikipedia.org/wiki/Earth_radius
        $An = $WGS84_a * $WGS84_a * cos(deg2rad($spot->latitude));
        $Bn = $WGS84_b * $WGS84_b * sin(deg2rad($spot->latitude));
        $Ad = $WGS84_a * cos($spot->latitude);
        $Bd = $WGS84_b * sin($spot->latitude);

        return sqrt(($An * $An + $Bn * $Bn) / ($Ad * $Ad + $Bd * $Bd));
    }

    static function get_bounding_box_for_radius(midgardmvc_helper_location_spot $spot, $radius)
    {
        $lat = deg2rad($spot->latitude);
        $lon = deg2rad($spot->longitude);
        $halfside = 1000 * $radius;

        // Radius of Earth at given latitude
        $radius = midgardmvc_helper_location_utils::get_earth_radius_at($spot);
        // Radius of the parallel at given latitude
        $pradius = $radius * cos($lat);

        $x1 = rad2deg($lat - $halfside / $radius);
        $x2 = rad2deg($lat + $halfside / $radius);
        $y1 = rad2deg($lon - $halfside / $pradius);
        $y2 = rad2deg($lon + $halfside / $pradius);

        $bbox = array
        (
            new midgardmvc_helper_location_spot($x1, $y1),
            new midgardmvc_helper_location_spot($x2, $y2),
        );
        
        return $bbox;
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
            case 'midgardmvc_helper_location_log':
            case 'midgardmvc_helper_location_city':
            case 'midgardmvc_helper_location_aerodrome':
                // Real position entry, query it directly
                $classname = $class;
                break;
            default:
                // Non-positioning MidCOM DBA object, query it through location cache
                $classname = 'midgardmvc_helper_location_location';
                break;
        }
        return $classname;
    }

    /**
     * Get closest items
     *
     * @param string $class MidCOM DBA class to query
     * @param midgardmvc_helper_location_spot $spot Center position
     * @param integer $limit How many results to return
     * @return Array Array of MidCOM DBA objects sorted by proximity
     */
    static function get_closest($class, midgardmvc_helper_location_spot $spot, $limit, $modifier = 0.15)
    {
        $classname = midgardmvc_helper_location_utils::get_positioning_class($class);
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
                    $result_spot = new midgardmvc_helper_location_spot($result);

                    $distance = sprintf("%05d", round(midgardmvc_helper_location_utils::get_distance($spot, $result_spot)));

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
            return midgardmvc_helper_location_utils::get_closest($class, $spot, $limit, $modifier);
        }

        $results = $qb->execute();
        $closest = array();
        foreach ($results as $result)
        {
            $result_spot = new midgardmvc_helper_location_spot($result);
            $distance = sprintf("%05d", round(midgardmvc_helper_location_utils::get_distance($spot, $result_spot)));

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
