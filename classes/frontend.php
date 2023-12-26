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
 * Availability skills - Front-end class.
 *
 * @package   availability_skills
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace availability_skills;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class.
 *
 * @package   availability_skills
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {

     /**
     * @var array Cached init parameters
     */
    protected $cacheinitparams = [];

    protected function get_javascript_strings() {
        return [
            'label_chooseskill',
            'label_choosetype',
            'label_chooselevel',
            'label_points',
            'type_notinlevel',
            'type_exactlevel',
            'type_selectlevelorhigher',
            'type_selectlevelorlower',
            'type_exactpoints',
            'type_moreorequalpoints',
            'type_lesspoints',
        ];
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
            $skilldata = condition::fetch_skill_record($course->id);
            $this->cacheinitparams = [$skilldata];
            return $this->cacheinitparams;
    }

    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {
        return true;
    }

}