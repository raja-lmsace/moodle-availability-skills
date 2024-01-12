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
use core_xapi\local\state;
use moodle_exception;


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
    private $skill;

    /** @var string Condition Type */
    private $conditiontype;

    /** @var int Level id*/
    private $level;

    /** @var int Points*/
    private $points;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {

        // Get Skill id.
        if (isset($structure->skill) && is_int($structure->skill)) {
            $this->skill = $structure->skill;
        } else {
            throw new \coding_exception('Missing or invalid ->skill for skills condition');
        }

        $conditiontypes = [
            self::TYPE_NOT_IN_LEVEL,
            self::TYPE_EXACT_LEVEL,
            self::TYPE_SELECT_LEVEL_OR_HIGHER,
            self::TYPE_SELECT_LEVEL_OR_LOWER,
            self::TYPE_EXACT_POINTS,
            self::TYPE_MORE_OR_EQUAL_POINTS,
            self::TYPE_LESS_POINTS,
        ];

        // Get condition type.
        if (isset($structure->ct) && in_array($structure->ct, $conditiontypes, true)) {
            $this->conditiontype = $structure->ct;
        } else {
            throw new \coding_exception('Missing or invalid ->condition type for skills condition');
        }

        // Get the level id.
        if (isset($structure->level) && is_int($structure->level)) {
            $this->level = $structure->level;
        }

        // Get the points.
        if (isset($structure->points) && is_int($structure->points)) {
            $this->points = $structure->points;
        }
    }

    /**
     * Save data.
     *
     * @return \stdClass
     */
    public function save() {
        // Save back the data into a plain array similar to $structure above.
        return (object)
        [
            'type' => 'skills',
            'skill' => $this->skill,
            'ct' => $this->conditiontype,
            'level' => $this->level,
            'points' => $this->points,
        ];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $skill skills ID
     * @param int $conditiontype Condition type
     * @param int $level level ID
     * @param int $points Points
     * @return stdClass Object representing condition
     */
    public static function get_json(int $skill, int $conditiontype, int $level, int $points) {
        return (object) [
            'type' => 'skills',
            'skill' => (int) $skill,
            'ct' => (int) $conditiontype,
            'level' => (int) $level,
            'points' => (int) $points,
        ];
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The $not option is potentially confusing. This option always indicates
     * the 'real' value of NOT. For example, a condition inside a 'NOT AND'
     * group will get this called with $not = true, but if you put another
     * 'NOT OR' group inside the first group, then a condition inside that will
     * be called with $not = false. We need to use the real values, rather than
     * the more natural use of the current value at this point inside the tree,
     * so that the information displayed to users makes sense.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        global $USER, $DB;

        $allow = self::skill_type_condition_met($this->skill, $this->conditiontype, $this->level, $this->points, $USER->id);
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
        global $DB;

        $str = 'requires_';
        $courseid = $info->get_course()->id;

        // Check the skill available for this course.
        if (!$DB->record_exists('tool_skills_courses', ['courseid' => $courseid, 'status' => '1', 'skill' => $this->skill])) {
            return get_string('missingskillsincourse', 'availability_skills');
        }

        if ((!$DB->record_exists('tool_skills', ['id' => $this->skill, 'status' => '1', 'archived' => '0']))) {
            return get_string('missingskills', 'availability_skills');
        }

        $a = new \stdClass();
        $a->skill = self::get_skill($this->skill);
        if ($record = $DB->get_record('tool_skills_levels', ['id' => $this->level, 'skill' => $this->skill])) {
            $a->level = $record->name;
        }
        $a->points = $this->points;

        if ($not) {
            $str .= self::get_lang_string_keyword( $this->conditiontype);
        } else {
            $str .= self::get_lang_string_keyword($this->conditiontype);
        }

        return get_string($str, 'availability_skills', $a);
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
     * Fetch the course skills records.
     *
     * @param int $courseid Course id.
     * @return void
     */
    public static function fetch_skill_record($courseid) {
        global $DB;

        // Fetch the skill record.
        $sql = 'SELECT ts.id as id, ts.name as name
        FROM {tool_skills} ts
        JOIN {tool_skills_courses} tcs ON tcs.skill = ts.id AND tcs.courseid = :courseid
        WHERE ts.archived != 1 AND ts.status <> 0 AND tcs.status <> 0';

        $params = ['courseid' => $courseid];
        $skills = $DB->get_records_sql($sql, $params);

        return $skills;
    }

    /**
     * Get the user points.
     *
     * @param int $skill Skill ID
     * @param int $userid releated User ID
     */
    public static function get_user_level($skill, $userid) {
        global $DB;
        $record = $DB->get_record('tool_skills_userpoints', ['skill' => $skill, 'userid' => $userid]);

        return (!empty($record->points)) ? $record->points : 0;
    }

    /**
     * Fetch the skills data record.
     *
     * @param int $skill Skill ID
     */
    public static function get_skill($skill) {
        global $DB;
        $skillrecord = $DB->get_record('tool_skills', ['id' => $skill]);

        return (!empty($skillrecord->name)) ? $skillrecord->name : '';
    }

    /**
     * Return true if the skill type meet required conditions, false otherwise.
     *
     * @param int $skill Skill ID
     * @param int $conditiontype Condtion type
     * @param int $level Level ID
     * @param int $points points
     * @param int $userid current user id.
     */
    protected static function skill_type_condition_met($skill, $conditiontype, $level, $points, $userid) {
        global $DB, $USER;

        // Get the user level points from the user points table.
        $userlevel = self::get_user_level($skill, $userid);
        $condtionlevel = '';

        // Get the condition level points.
        if ($record = $DB->get_record('tool_skills_levels', ['id' => $level, 'skill' => $skill])) {
            $condtionlevel = $record->points;
        }
        $typeconditionmet = true;
        switch($conditiontype) {
            case self::TYPE_NOT_IN_LEVEL:
                if ($userlevel !== $condtionlevel) {
                    $typeconditionmet = false;
                }
                break;
            case self::TYPE_EXACT_LEVEL:
                if ($userlevel === $condtionlevel) {
                    $typeconditionmet = false;
                }
                break;
            case self::TYPE_SELECT_LEVEL_OR_HIGHER:
                if ($userlevel >= $condtionlevel) {
                    $typeconditionmet = false;
                }
                break;
            case self::TYPE_SELECT_LEVEL_OR_LOWER:
                if ($userlevel <= $condtionlevel) {
                    $typeconditionmet = false;
                }
                break;
            case self::TYPE_EXACT_POINTS:
                if ($userlevel == $points) {
                    $typeconditionmet = false;
                }
                break;
            case self::TYPE_MORE_OR_EQUAL_POINTS:
                if ($userlevel >= $points) {
                    $typeconditionmet = false;
                }
                break;
            case self::TYPE_LESS_POINTS:
                if ($userlevel < $points) {
                    $typeconditionmet = false;
                }
                break;
        }
        return $typeconditionmet;
    }

    /**
     * Return the condition types language string keyword.
     *
     * @param int $conditiontype Condition Type
     * @return string
     */
    public static function get_lang_string_keyword($conditiontype) {
        switch ($conditiontype) {
            case self::TYPE_NOT_IN_LEVEL:
                return 'notinlevel';
            case self::TYPE_EXACT_LEVEL:
                return 'exactlevel';
            case self::TYPE_SELECT_LEVEL_OR_HIGHER:
                return 'selectlevelorhigher';
            case self::TYPE_SELECT_LEVEL_OR_LOWER:
                return 'selectlevelorlower';
            case self::TYPE_EXACT_POINTS:
                return 'exactpoints';
            case self::TYPE_MORE_OR_EQUAL_POINTS:
                return 'moreorequalpoints';
            case self::TYPE_LESS_POINTS:
                return 'lesspoints';
            default:
                throw new \coding_exception('Unexpected Condition type: ' . $conditiontype);
        }
    }
}
