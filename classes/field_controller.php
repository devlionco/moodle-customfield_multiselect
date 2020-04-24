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
 * Class field
 *
 * @package customfield_file
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */

namespace customfield_file;

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package customfield_file
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Customfield type
     */
    const TYPE = 'file';

    /**
     * Add fields for editing a file field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        global $CFG;

        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_file'));
        $mform->setExpanded('header_specificsettings', true);

        $mform->addElement('filetypes', 'configdata[allowedtypes]', get_string('allowedtypes', 'customfield_file'));

        $maxbytesoptions = get_max_upload_sizes($CFG->maxbytes);

        $mform->addElement('select', 'configdata[maxbytes]', get_string('maxbytes', 'customfield_file'), $maxbytesoptions);
        $mform->setType('configdata[maxbytes]', PARAM_INT);

        $options[0] = get_string('unlimited');
        for ($i = 1; $i <= 10; $i++) {
            $options[$i] = $i;
        }

        $mform->addElement('select', 'configdata[maxfiles]', get_string('maxfiles', 'customfield_file'), $options);
        $mform->setDefault('configdata[maxfiles]', 1);
    }

    /**
     * Does this custom field type support being used as part of the block_myoverview
     * custom field grouping?
     * @return bool
     */
    public function supports_course_grouping(): bool {
        return false;
    }
}
