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
 * Availability skills - Language strings.
 *
 * @package   availability_skills
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['description'] = 'Require students to meet the skills conditions.';
$string['pluginname'] = 'Restrict by skills';
$string['title'] = 'Skills';

// Condition type strings.
$string['chooseskill'] = 'Choose Skill';
$string['choosetype'] = 'Type';
$string['chooselevel'] = 'Level';
$string['points'] = 'Points';
$string['notinlevel'] = 'Not in level';
$string['exactlevel'] = 'Exact level';
$string['selectlevelorhigher'] = 'Selected level or higher';
$string['selectlevelorlower'] = 'Selected level or lower';
$string['exactpoints'] = 'Exact points';
$string['moreorequalpoints'] = 'More or equal than points';
$string['lesspoints'] = 'Less points';

// Condition Type description.
$string['requires_notinlevel'] = 'Your skill <strong>{$a->skill}</strong> should not be at level - <strong>{$a->level}</strong>';
$string['requires_exactlevel'] = 'Your skill <strong>{$a->skill}</strong> should be precisely at level - <strong>{$a->level}</strong>';
$string['requires_selectlevelorhigher'] = 'Ensure that your skill <strong>{$a->skill}</strong> is at an equal or higher than level - <strong>{$a->level}</strong>';
$string['requires_selectlevelorlower'] = 'Ensure that your skill <strong>{$a->skill}</strong> is at or lower than level - <strong>{$a->level}</strong>';
$string['requires_exactpoints'] = 'Your skill <strong>{$a->skill}</strong> should be exactly <strong>{$a->points}</strong> points';
$string['requires_moreorequalpoints'] = 'Your skill <strong>{$a->skill}</strong> should be at or above <strong>{$a->points}</strong> points';
$string['requires_lesspoints'] = 'Your skill <strong>{$a->skill}</strong> should be below <strong>{$a->points}</strong> points ';
$string['error_select_skill_id'] = 'You must be select the skill';

// Errors description.
$string['missingskillsincourse'] = 'The selected skill is <strong>missing</strong> for this actvity';
$string['missingskills'] = 'The selected skill is <strong>missing</strong>';
