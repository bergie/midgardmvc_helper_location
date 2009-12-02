<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * @package midgardmvc_helper_location
 */
interface midgardmvc_helper_location_geocoder
{
    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param array $location Parameters to geocode with, conforms to XEP-0080
     * @return midgardmvc_helper_location_spot containing geocoded information
     */
    public function geocode(array $location);
}
