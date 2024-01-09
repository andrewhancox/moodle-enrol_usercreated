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

use advanced_testcase;
use context_course;

class plugin_test extends advanced_testcase {

    protected function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['usercreated'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    protected function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['usercreated']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    public function test_enable() {
        $this->resetAfterTest();

        static::assertFalse(enrol_is_enabled('usercreated'));
        $this->enable_plugin();
        static::assertTrue(enrol_is_enabled('usercreated'));
    }

    public function test_enrol() {
        global $DB;

        $this->resetAfterTest();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $course1 = static::getDataGenerator()->create_course();
        $course2 = static::getDataGenerator()->create_course();

        $shouldbeenrolled = [$course1->id => [], $course2->id => []];
        $shouldnotbeenrolled = [$course1->id => [], $course2->id => []];

        $user1 = static::getDataGenerator()->create_user();
        $shouldnotbeenrolled[$course1->id][] = $user1->id;
        $shouldnotbeenrolled[$course2->id][] = $user1->id;

        $this->check($shouldbeenrolled, $shouldnotbeenrolled);

        $this->enable_plugin();

        $user2 = static::getDataGenerator()->create_user();
        $shouldnotbeenrolled[$course1->id][] = $user2->id;
        $shouldnotbeenrolled[$course2->id][] = $user2->id;

        $this->check($shouldbeenrolled, $shouldnotbeenrolled);

        enrol_get_plugin('usercreated')->add_instance($course1, [
            'courseid' => $course1->id,
            'roleid' => $studentrole->id,
        ]);

        $user3 = static::getDataGenerator()->create_user();
        $shouldbeenrolled[$course1->id][] = $user3->id;
        $shouldnotbeenrolled[$course2->id][] = $user3->id;

        $this->check($shouldbeenrolled, $shouldnotbeenrolled);

        $this->disable_plugin();

        $user4 = static::getDataGenerator()->create_user();
        $shouldnotbeenrolled[$course1->id][] = $user4->id;
        $shouldnotbeenrolled[$course2->id][] = $user4->id;

        $this->check($shouldbeenrolled, $shouldnotbeenrolled);
    }

    private function check($shouldbeenrolled, $shouldnotbeenrolled) {
        foreach ($shouldbeenrolled as $courseid => $users) {
            foreach ($users as $user) {
                static::assertTrue(is_enrolled(context_course::instance($courseid), $user));
            }
        }
        foreach ($shouldnotbeenrolled as $courseid => $users) {
            foreach ($users as $user) {
                static::assertFalse(is_enrolled(context_course::instance($courseid), $user));
            }
        }
    }
}
