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
 * Handles AJAX processing.
 *
 * @package   availability_skills
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * return the selected skill levels in the fragment
 *
 * @param array $args Skill data
 * @return string $html
 */
function availability_skills_output_fragment_load_skill_levels($args) {
    global $DB;
    // TODO: Verify the context.
    if (isset($args['skill'])) {
        $skillid = $args['skill'];
        $levels = $DB->get_records_menu('tool_skills_levels', ['skill' => $skillid], '', 'id, name');
        $html = '';
        foreach ($levels as $id => $name) {
            $html .= html_writer::tag('option', format_string($name), ['value' => $id]);
        }
        return $html;
    }
}
