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
 * File plugin data controller
 *
 * @package customfield_file
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */

namespace customfield_file;

defined('MOODLE_INTERNAL') || die;

use core_customfield\field_controller;
/**
 * Class data
 *
 * @package customfield_file
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */
class data_controller extends \core_customfield\data_controller {

    var $options = [];


    public function __construct(int $id, \stdClass $record) {
        parent::__construct($id, $record);
        $field = field_controller::create($record->fieldid);
        $config = $field->get('configdata');

        $this->options = [
            'maxbytes' => $config['maxbytes'],
            'accepted_types' => $config['allowedtypes'],
            'subdirs' => 0,
            'maxfiles' => $config['maxfiles'],
            'context' => $this->get_context()
        ];
    }

    /**
     * Return the name of the field where the information is stored
     * @return string
     */
    public function datafield() : string {
        return 'charvalue';
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        return '';
    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {

        $elementname = $this->get_form_element_name();
        $elementnameprepare = $this->get_form_element_name_prepare();

        $contextid = $this->get_context()->id;

        $data = new \stdClass();

        file_prepare_standard_filemanager($data, $elementnameprepare, $this->options, $this->get_context(), 'customfield_file', $elementnameprepare, $contextid);

        $mform->addElement('filemanager', $elementnameprepare, $this->get_field()->get_formatted_name(), null, $this->options);

        $mform->setDefault($elementnameprepare, $data->$elementname);
    }

    public function instance_form_save(\stdClass $datanew) {

        $elementname = $this->get_form_element_name_prepare();
        if (!property_exists($datanew, $elementname)) {
            return;
        }

        $value = $datanew->$elementname;

        $fs = get_file_storage();
        $contextid = $this->get_context()->id;

        $draftlinks = file_save_draft_area_files($value, $contextid, 'customfield_file', $elementname, $contextid, $this->options);

        $files = $fs->get_area_files($contextid, 'customfield_file', $elementname, $contextid, 'itemid, filepath, filename', false);
        $data = [];
        foreach ($files as $pathnamehash => $file) {
            $data[] = $file->get_id();
        }

        $data = serialize($data);
        $this->data->set($this->datafield(), $data);
        $this->data->set('value', $data);
        $this->save();
    }

    public function get_value() {
        if (!$this->get('id')) {
            return [$this->get_default_value()];
        }
        return unserialize($this->get($this->datafield()));
    }

    /**
     * Returns value in a human-readable format
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $value = $this->get_value();

        if ($this->is_empty($value)) {
            return null;
        }

        $fs = get_file_storage();
        $links = [];

        foreach ($value as $fileid) {
            $file = $fs->get_file_by_id($fileid);
            $links[] = (\moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                '/',
                $file->get_filename())->out()
            );
        }

        return $links;
    }

    /**
     * Returns the name of the field to be used on HTML forms.
     *
     * @return string
     */
    protected function get_form_element_name() : string {
        return $this->get_form_element_name_prepare(). '_filemanager';
    }

    protected function get_form_element_name_prepare() : string {
        return 'customfield_' . $this->get_field()->get('shortname');
    }
}
