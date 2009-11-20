<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Spot class for handling non-persistent geographical location.
 *
 * For persistent locations use the org_routamc_positioning_location class that is connected 
 * to some object.
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_spot
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
     * Timestamp of the spot
     *
     * @var midgard_datetime
     */
    public $when = null;
    
    public function __construct($arg1, $arg2 = null)
    {
        if (is_object($arg1))
        {
            $this->latitude = $arg1->latitude;
            $this->longitude = $arg1->longitude;
        }
        else
        {
            $this->latitude = $arg1;
            $this->longitude = $arg2;
        }
    }
}
?>
