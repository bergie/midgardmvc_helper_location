<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Spot class for handling a geographical location
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_spot
{
    public $latitude;
    public $longitude;
    
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