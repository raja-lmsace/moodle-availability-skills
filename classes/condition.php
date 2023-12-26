<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Availability skills - Condition main class..
 *
 * @package   availability_skills
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace availability_skills;

use core_availability\info;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package   availability_skills
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var int Type: Not in level */
    const TYPE_NOT_IN_LEVEL = 0;

    /** @var int Type: Exact level */
    const TYPE_EXACT_LEVEL = 1;

    /** @var int Type: Select Level Or Higher */
    const TYPE_SELECT_LEVEL_OR_HIGHER = 2;

    /** @var int Type: Select Level Or Lower */
    const TYPE_SELECT_LEVEL_OR_LOWER = 3;

    /** @var int Type: Exact Points */
    const TYPE_EXACT_POINTS = 4;

    /** @var int Type: More Or Equal than Points */
    const TYPE_MORE_OR_EQUAL_POINTS = 5;

    /** @var int Type: Less Points */
    const TYPE_LESS_POINTS = 6;

    /** @var int Selected skill id */
    private $skills;

    /** @var string Condition Type */
    private $conditiontype;

    /** @var int Level id*/
    private $level;

    /** @var int Points*/
    private $points;

    public function __construct($structure) {
        // Get Skill id.
        if (isset($structure->skillid) && is_int($structure->skillid)) {
            $this->skills = $structure->skillid;
        } else {
            throw new \coding_exception('Missing or invalid ->skillid for skills condition');
        }

        // Get condition type.
        if (isset($structure->ct) && in_array($structure->ct, array(self::TYPE_NOT_IN_LEVEL,
            self::TYPE_EXACT_LEVEL, self::TYPE_SELECT_LEVEL_OR_HIGHER, self::TYPE_SELECT_LEVEL_OR_LOWER,
            self::TYPE_EXACT_POINTS, self::TYPE_MORE_OR_EQUAL_POINTS, self::TYPE_LESS_POINTS), true)) {
            $this->conditiontype = $structure->ct;
        } else {
            throw new \coding_exception('Missing or invalid ->ct for skills condition');
        }

        // Get the level id.
        if (isset($structure->level) && is_int($structure->level)) {
            $this->level = $structure->level;
        } else {
            throw new \coding_exception('Missing or invalid ->level for skills condition');
        }

        // Get the points.
        if (isset($structure->points) && is_int($structure->points)) {
            $this->points = $structure->points;
        } else {
            throw new \coding_exception('Missing or invalid ->points for skills condition');
        }
    }

    public function save() {
        // Save back the data into a plain array similar to $structure above.
        return (object)
        [
            'type' => 'skills',
            'skills' => $this->skills,
            'ct' => $this->conditiontype,
            'level' => $this->level,
            'points' => $this->points,
        ];
    }

    public function is_available($not, info $info, $grabthelot, $userid) {
            $allow = true;
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies).
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @return string Information string (for admin) about all restrictions on
     *   this item
     */
    public function get_description($full, $not, info $info) {

        $str = 'requires_';
        if ($not) {
            // Convert NOT strings to use the equivalent where possible.
            switch ($this->conditiontype) {
                case self::TYPE_NOT_IN_LEVEL:
                    $str .= 'not_in_level';
                    break;
                case self::TYPE_EXACT_LEVEL:
                    $str .= 'exact_level';
                    break;
                case self::TYPE_SELECT_LEVEL_OR_HIGHER:
                    $str .= 'select_level_or_higher';
                    break;
                case self::TYPE_SELECT_LEVEL_OR_LOWER:
                    $str .= 'select_level_or_lower';
                    break;
                case self::TYPE_EXACT_POINTS;
                    $str .= 'exact_points';
                    break;
                case self::TYPE_MORE_OR_EQUAL_POINTS:
                    $str .= 'more_or_equal_points';
                    break;
                case self::TYPE_LESS_POINTS:
                    $str .= 'less_points';
                    break;
                default:
                    // The other two cases do not have direct opposites.
                    $str .= '';
                    break;
            }
        }
        return get_string($str, 'availability_skills');
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string(): string {
        switch ($this->conditiontype) {
            case self::TYPE_NOT_IN_LEVEL:
                $type = 'Not In Level';
                break;
            case self::TYPE_EXACT_LEVEL:
                $type = 'Exact Level';
                break;
            case self::TYPE_SELECT_LEVEL_OR_HIGHER:
                $type = 'Select Level Or Higher';
                break;
            case self::TYPE_SELECT_LEVEL_OR_LOWER:
                $type = 'Select Level Or Lower';
                break;
                case self::TYPE_EXACT_POINTS;
                $type = 'Exact Points';
                break;
            case self::TYPE_MORE_OR_EQUAL_POINTS:
                $type = 'More Or Equal Points';
                break;
            case self::TYPE_LESS_POINTS:
                $type = 'Less Points';
                break;
            default:
                throw new \coding_exception('Unexpected skill condition');
        }
        return $type;
    }

    /**
     *
     */
    public static function fetch_skill_record($courseid) {
        global $DB;
        // Fetch the skill record.
        $sql = 'SELECT ts.id as skillid, ts.name as skillname
        FROM {tool_skills} ts
        JOIN {tool_skills_courses} tcs ON tcs.skill = ts.id AND tcs.courseid = :courseid
        WHERE ts.archived != 1 AND ts.status <> 0';
        $params = ['courseid' => $courseid];
        $skills = $DB->get_records_sql($sql, $params);
        return $skills;
    }

}