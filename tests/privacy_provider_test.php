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
 * Privacy API tests for qtype_varnumunit
 *
 * @package qtype_varnumunit
 * @copyright 2018 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_varnumunit;

use core_privacy\local\request\writer;
use qtype_varnumunit\privacy\provider;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/varnumunit/classes/privacy/provider.php');

/**
 * Privacy provider tests class.
 *
 * @package   qtype_varnumunit
 * @copyright 2021 The Open university
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \qtype_varnumunit\privacy\provider
 */
final class privacy_provider_test extends \core_privacy\tests\provider_testcase {
    // Include the privacy helper which has assertions on it.

    public function test_get_metadata(): void {
        $collection = new \core_privacy\local\metadata\collection('qtype_varnumunit');
        $actual = provider::get_metadata($collection);
        $this->assertEquals($collection, $actual);
    }

    public function test_export_user_preferences_no_pref(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test the export_user_preferences given different inputs
     * @dataProvider user_preference_provider

     * @param string $name The name of the user preference to get/set
     * @param string $value The value stored in the database
     * @param string $expected The expected transformed value
     */
    public function test_export_user_preferences($name, $value, $expected): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        set_user_preference("qtype_varnumunit_$name", $value, $user);
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $this->assertTrue($writer->has_any_data());
        $preferences = $writer->get_user_preferences('qtype_varnumunit');
        foreach ($preferences as $key => $pref) {
            $preference = get_user_preferences("qtype_varnumunit_{$key}", null, $user->id);
            if ($preference === null) {
                continue;
            }
            $desc = get_string("privacy:preference:{$key}", 'qtype_varnumunit');
            $this->assertEquals($expected, $pref->value);
            $this->assertEquals($desc, $pref->description);
        }
    }

    /**
     * Create an array of valid user preferences for the Variable numeric set with units question type.
     *
     * @return array Array of valid user preferences.
     */
    public static function user_preference_provider(): array {
        return [
                'default mark 1' => ['defaultmark', 1, 1],
                'penalty 33.33333%' => ['penalty', 0.3333333, '33.33333%'],
                'unitfraction 0.0500000' => ['unitfraction', 0.0500000, 'Value : 95%, Units : 5%'],
                'unitfraction 0.1100000' => ['unitfraction', 0.1000000, 'Value : 90%, Units : 10%'],
                'unitfraction 0.1111111' => ['unitfraction', 0.1111111, 'Value : 88.88889%, Units : 11.11111%'],
                'unitfraction 0.1250000' => ['unitfraction', 0.1250000, 'Value : 87.5%, Units : 12.5%'],
                'unitfraction 0.1666667' => ['unitfraction', 0.1666667, 'Value : 83.33333%, Units : 16.66667%'],
                'unitfraction 0.2000000' => ['unitfraction', 0.2000000, 'Value : 80%, Units : 20%'],
        ];
    }
}
