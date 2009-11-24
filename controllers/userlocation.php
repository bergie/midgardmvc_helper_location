<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Controller for getting and updating user's location
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_controllers_userlocation
{
    public function __construct(midcom_core_component_interface $instance)
    {
        $this->configuration = $instance->configuration;
    }

    /**
     * Update user's location
     */
    public function post_location(array $args)
    {
        $spot = new org_routamc_positioning_spot($_POST['latitude'], $_POST['longitude']);
        
        if (!org_routamc_positioning_user::set_location($spot))
        {
            throw new midcom_exception_httperror("Failed to store location");
        }
        
        $midcom->log("postlocation", "Stored {$log->guid}" . $midcom->dispatcher->get_midgard_connection()->get_error_string(), 'warn');
        
        $this->get_location($args);
    }

    /**
     * Read user's location
     */
    public function get_location(array $args)
    {
        $this->data = org_routamc_positioning_user::get_location();
    }
}
?>