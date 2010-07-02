<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Spot class for handling non-persistent geographical location.
 *
 * For persistent locations use the midgardmvc_helper_location_location class that is connected 
 * to some object.
 *
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_spot
{
    /**
     * WGS-84 latitude of the spot
     *
     * @var float
     */
    public $latitude = 0.0;

    /**
     * WGS-84 latitude of the spot
     *
     * @var float
     */
    public $longitude = 0.0;

    /**
     * Approximate accuracy of the spot:
     *
     * - 10, `exact`:     Spot is accurate down to a few meters (for example from GPS)
     * - 20, `postal`:    Spot is accurate down to few hundred meters (for example from a Google Maps click)
     * - 30, `city`:      Spot is approximate based on a city name
     * - 50, `state`:     Spot is somewhere in a state
     * - 60, `country`:   Spot is somewhere in a country
     * - 70, `continent`: Spot is somewhere in a continent
     * - 80, `planet`:    Spot is somewhere on a planet
     */
    public $accuracy = 30;

    /**
     * Textual description of the spot
     */
    public $text = '';

    /**
     * Timestamp of the spot
     *
     * @var midgard_datetime
     */
    public $when = null;

    /**
     * Where the spot comes from
     */
    public $source = '';

    public function __construct($arg1, $arg2 = null)
    {
        if (   is_object($arg1)
            && is_null($arg2))
        {
            if (!is_a($arg1, 'midgard_object'))
            {
                throw new InvalidArgumentException("You can instantiate spots only from MgdSchema objects");
            }
            $this->latitude = $arg1->latitude;
            $this->longitude = $arg1->longitude;
            
            if (isset($arg1->accuracy))
            {
                $this->accuracy = $arg1->accuracy;
            }
       
            $this->when = $arg1->metadata->created;
        }
        elseif (   is_string($arg1)
                && is_null($arg2))
        {
            $this->text = $arg1;
            $this->accuracy = 80;
        }
        else
        {
            if (   !is_float($arg1)
                || !is_float($arg2))
            {
                throw new InvalidArgumentException("A pair of WGS-84 coordinates expected");
            }
            
            $this->latitude = $arg1;
            $this->longitude = $arg2;
        }
        
        if (   $this->latitude > 90
            || $this->latitude < -90)
        {
            throw new InvalidArgumentException("WGS-84 latitude must be between 90 and -90 degrees");
        }

        if (   $this->longitude > 180
            || $this->longitude < -180)
        {
            throw new InvalidArgumentException("WGS-84 longitude must be between 180 and -180 degrees");
        }
    }

    /**
     * Get distance to another position in kilometers or nautical miles
     *
     * Code from http://www.corecoding.com/getfile.php?file=25
     */
    public function distance_to(midgardmvc_helper_location_spot $to, $unit = 'K', $round = true)
    {
        $theta = $this->longitude - $to->longitude;
        $dist = sin(deg2rad($this->latitude)) * sin(deg2rad($to->latitude)) + cos(deg2rad($this->latitude)) * cos(deg2rad($to->latitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $dist = $dist * 60 * 1.1515;

        if ($unit == 'K')
        {
            $dist *= 1.609344;
        }
        else if ($unit == 'N')
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
     * Get bearing to another position
     *
     * Code from http://www.corecoding.com/getfile.php?file=25
     */
    public function bearing_to(midgardmvc_helper_location_spot $to)
    {
        if (round($this->longitude, 1) == round($to->longitude, 1))
        {
            if ($this->latitude < $to->latitude)
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
            $dist = $this->get_distance($to, 'N');
            $arad = acos((sin(deg2rad($to->latitude)) - sin(deg2rad($this->latitude)) * cos(deg2rad($dist / 60))) / (sin(deg2rad($dist / 60)) * cos(deg2rad($this->latitude))));
            $bearing = $arad * 180 / pi();
            if (sin(deg2rad($to->longitude - $this->longitude)) < 0)
            {
                $bearing = 360 - $bearing;
            }
        }

        return round($bearing);
    }

    /**
     * Get direction (North, East, South, West) to another location.
     *
     */
    public function direction_to(midgardmvc_helper_location_spot $to)
    {
        $bearing = $this->bearing_to($to);

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
}
?>
