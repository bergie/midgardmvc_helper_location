<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Positioning for a given Midgard object
 *
 * <b>Example:</b>
 *
 * <code>
 * <?php
 * $object_position = new org_routamc_positioning_object($article);
 * $coordinates = $object_position->get_coordinates();
 * if (!is_null($coordinates))
 * {
 *     echo "<meta name=\"icbm\" content=\"{$coordinates['latitude']},{$coordinates['longitude']}\" />\n";
 * }
 * ?>
 * </code>
 *
 * @package org_routamc_positioning
 */
class org_routamc_positioning_object
{
    /**
     * The object we're looking for position of
     *
     * @var midgard_object
     */
    private $object = null;

    function __construct($object)
    {
         $this->object = $object;
    }

    /**
     * Get location object for the object
     *
     * @return org_routamc_positioning_location
     */
    function seek_location_object()
    {
        $qb = org_routamc_positioning_location::new_query_builder();
        $qb->add_constraint('parent', '=', $this->object->guid);
        $qb->begin_group('OR');
            $qb->add_constraint('relation', '=', org_routamc_positioning::RELATION_IN);
            $qb->add_constraint('relation', '=', org_routamc_positioning::RELATION_LOCATED);
        $qb->end_group();
        $qb->add_order('metadata.published', 'DESC');
        $matches = $qb->execute();
        if (count($matches) == 0)
        {
            return null;
        }
        return $matches[0];
    }

    /**
     * Get log object based on creation time and creator of the object
     *
     * @return org_routamc_positioning_log
     */
    function seek_log_object($person = null, $time = null)
    {
        if (   is_integer($person)
            || is_string($person))
        {
            $person_guid = $person;
        }
        elseif (is_null($person))
        {
            $person_guid = $this->object->metadata->creator;
            // TODO: Use metadata.authors?
        }
        else
        {
            $person_guid = $person->guid;
        }

        if (is_null($time))
        {
            $time = $this->object->metadata->published;
        }

        $person = new midgard_person($person_guid);

        $qb = org_routamc_positioning_log::new_query_builder();
        $qb->add_constraint('person', '=', $person->id);
        $qb->add_constraint('date', '<=', $time);
        $qb->add_order('date', 'DESC');
        $qb->set_limit(1);
        $matches = $qb->execute();

        if (count($matches) > 0)
        {
            return $matches[0];
        }
        return null;
    }

    /**
     * Get coordinates of the object
     *
     * @return org_routamc_positioning_spot
     */
    function get_coordinates($person = null, $time = null, $cache = true)
    {
        if (   is_a($this->object, 'midgard_person')
            || is_a($this->object, 'org_openpsa_person'))
        {
            // This is a person record. Seek log
            $user_position = new org_routamc_positioning_person($this->object);
            return $user_position->get_coordinates($time);
        }

        if (is_null($time))
        {
            if (!isset($this->object->metadata->published))
            {
                //return null;
                $time = time();
            }
            $time = $this->object->metadata->published;
        }

        // Check if the object has a location set
        $location = $this->seek_location_object();
        if (   is_object($location)
            && $location->guid)
        {
            $spot = new org_routamc_positioning_spot($location);

            // Consistency check
            if ($location->date != $time)
            {
                if ($location->log)
                {
                    // We are most likely pointing to wrong log. Remove this cached entry so we can recreate i
                    // again below
                    $location->delete();
                    $cache = true;
                }
                else
                {
                    // This location entry isn't coming from a log so it just needs to be rescheduled
                    $location->date = $time;
                    $location->update();
                    return $spot;
                }
            }
            else
            {
                return $spot;
            }
        }

        // No location set, seek based on creator and creation time
        $log = $this->seek_log_object($person, $time);
        if (is_object($log))
        {
            $spot = new org_routamc_positioning_spot($log);
            
            if ($cache)
            {
                // Cache the object's location into a location object
                $location = new org_routamc_positioning_location();
                $location->log = $log->id;
                $location->relation = (int) org_routamc_positioning::RELATION_IN;
                $location->date = $time;
                $location->parent = $this->object->guid;
                $location->parentclass = get_class($this->object);
                // TODO: Save parent component
                $location->latitude = $log->latitude;
                $location->longitude = $log->longitude;
                $location->altitude = $log->altitude;
                $location->create();
            }

            return $spot;
        }

        // No coordinates found, return null
        return null;
    }

    function set_metadata()
    {
        $coordinates = $this->get_coordinates();
        if (!is_null($coordinates))
        {
            // ICBM tag as defined by http://geourl.org/
            $_MIDCOM->add_meta_head
            (
                array
                (
                    'name' => 'icbm',
                    'content' => "{$coordinates['latitude']},{$coordinates['longitude']}",
                )
            );
        }
    }
}