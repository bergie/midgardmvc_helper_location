<?php
/**
 * @package midgardmvc_helper_location
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * User geolocation
 *
 * @package midgardmvc_helper_location
 */
class midgardmvc_helper_location_user
{
    static public function set_location(midgardmvc_helper_location_spot $location)
    {
        $midcom = midgardmvc_core::get_instance();
        if ($midcom->authentication->is_user())
        {
            // Set to user's location log
            return midgardmvc_helper_location_user::set_location_for_person($location, $midcom->authentication->get_person());
        }

        // Set to session
        $session = new midgardmvc_core_services_sessioning('midgardmvc_helper_location_user');
        return $session->set('location', $location);
    }
    
    static public function set_location_for_person(midgardmvc_helper_location_spot $spot, midgard_person $person)
    {
        // TODO: Check that we don't have a location matching this already from same day
        $log = new midgardmvc_helper_location_log();
        $log->person = $person->id;

        $log->latitude = $spot->latitude;
        $log->longitude = $spot->longitude;
        $log->text = $spot->text;
        if ($spot->source)
        {
            $log->importer = $spot->source;
        }
        if ($spot->accuracy)
        {
            $log->accuracy = $spot->accuracy;
        }

        if (!is_null($spot->when))
        {
            $log->metadata->published = $spot->when;
        }

        return $log->create();
    }

    static public function get_location(midgard_datetime $when = null)
    {
        $midcom = midgardmvc_core::get_instance();
        if ($midcom->authentication->is_user())
        {
            // Get from user's location log
            return midgardmvc_helper_location_user::get_location_for_person($midcom->authentication->get_person(), $when);
        }

        // Get from session
        $session = new midgardmvc_core_services_sessioning('midgardmvc_helper_location_user');
        if (!$session->exists('location'))
        {
            return null;
        }
        return $session->get('location');
    }
    
    static public function get_location_for_person(midgard_person $person, midgard_datetime $when = null)
    {
        $qb = new midgard_query_builder('midgardmvc_helper_location_log');
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
            $spot = new midgardmvc_helper_location_spot($log);
            $spot->source = $log->importer;
            $spot->text = $log->text;
            return $spot;
        }
        
        return null;
    }
}
