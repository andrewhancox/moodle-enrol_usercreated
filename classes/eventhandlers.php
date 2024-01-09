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
 * @package enrol_usercreated
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2023, Andrew Hancox
 */


namespace enrol_usercreated;

use core\event\user_created;
use core\plugininfo\enrol;

class eventhandlers {
    public static function user_created(user_created $event) {
        global $DB;

        if (!enrol::get_enabled_plugin('usercreated')) {
            return;
        }

        $plugin = enrol_get_plugin('usercreated');

        foreach ($DB->get_records('enrol', ['enrol' => 'usercreated']) as $instance) {
            $plugin->enrol_user($instance, $event->relateduserid, $instance->roleid, time());
        }
    }
}
