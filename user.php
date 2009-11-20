<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * User geolocation
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_user
{
    static public function set_location(org_routamc_positioning_spot $location, midgard_datetime $when = null)
    {
        $midcom = midcom_core_midcom::get_instance();
        if ($midcom->authentication->is_user())
        {
            // Set to user's location log
            return org_routamc_positioning_user::set_location_for_person($location, $midcom->authentication->get_person(), $when);
        }

        // Set to session
        $session = new midcom_core_services_sessioning('org_routamc_positioning_user');
        return $session->set('location', $location);
    }
    
    static public function set_location_for_person(org_routamc_positioning_spot $location, midgard_person $person, midgard_datetime $when = null)
    {
        // TODO: Check that we don't have a location matching this already from same day
        $log = new org_routamc_positioning_log();
        $log->person = $person->id;
        
        if (!is_null($when))
        {
            $log->metadata->published = $when;
        }
            
        // TODO: Get from spot
        $log->importer = 'userlocation';
        $log->accuracy = 20;
            
        $log->latitude = $spot->latitude;
        $log->longitude = $spot->longitude;
        return $log->create();
    }

    static public function get_location(midgard_datetime $when = null)
    {
        $midcom = midcom_core_midcom::get_instance();
        if ($midcom->authentication->is_user())
        {
            // Get from user's location log
            return org_routamc_positioning_user::get_location_for_person($midcom->authentication->get_person(), $when);
        }

        // Get from session
        $session = new midcom_core_services_sessioning('org_routamc_positioning_user');
        if (!$session->exists('location'))
        {
            return null;
        }
        return $session->get('location');
    }
    
    static public function get_location_for_person(midgard_person $person, midgard_datetime $when = null)
    {
        $qb = new midgard_query_builder('org_routamc_positioning_log');
        $qb->add_constraint('person', '=', $person->id);
            
        if (!is_null($when))
        {
            $qb->add_constraint('metadata.published', '<=', $when);
        }

        $qb->set_limit(1);
        $qb->add_order('metadata.published', 'DESC');
        $logs = $qb->execute();
        foreach ($logs as $log)
        {
            return org_routamc_positioning_spot($log->latitude, $log->longitude);
        }
        
        return null;
    }
}