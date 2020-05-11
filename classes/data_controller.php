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
 * Multielect plugin data controller
 *
 * @package customfield_multiselect
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */

namespace customfield_multiselect;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package customfield_multiselect
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */
class data_controller extends \customfield_select\data_controller {

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
     * Add fields for editing a multiselect field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $config = $field->get('configdata');
        $options = field_controller::get_options_array($field);
        $formattedoptions = array();
        $context = $this->get_field()->get_handler()->get_configuration_context();
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $formattedoptions[$key] = format_string($option, true, ['context' => $context]);
        }

        $elementname = $this->get_form_element_name();
        $options = array(
            'multiple' => true
        );
        $mform->addElement('autocomplete', $elementname, $this->get_field()->get_formatted_name(), $formattedoptions, $options);

        if (($defaultkey = array_search($config['defaultvalue'], $options)) !== false) {
            $mform->setDefault($elementname, $defaultkey);
        }
        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }
        $value = $datanew->$elementname;

        $value = serialize($value);
        $this->data->set($this->datafield(), $value);
        $this->data->set('value', $value);
        $this->save();
    }

    /**
     * Returns the unserialized value from the database or default value if data record is not present
     *
     * @return mixed
     */
    public function get_value() {
        if (!$this->get('id')) {
            return $this->get_default_value();
        }

        $value = unserialize($this->get($this->datafield()));

        foreach ($value as $key => $val) {
            if (empty($val)) {
                unset($value[$key]);
            }
        }

        return serialize($value);
    }

    /**
     * Returns value in a human-readable format or default value if data record is not present
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $value = $this->get_value();

        $value = unserialize($value);

        if ($this->is_empty($value)) {
            return '';
        }

        $fieldcontroller = $this->get_field();
        $options = $fieldcontroller::get_options_array($fieldcontroller);
        $return = [];

        foreach ($value as $optionid) {
            if (array_key_exists($optionid, $options) && !empty($options[$optionid])) {
                $return[] = format_string($options[$optionid], true, ['context' => $this->get_context()]);
            }
        }

        return implode(', ', $return);
    }

    /**
     * Checks if the value is empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function is_empty($value) : bool {
        return empty($value);
    }

    /**
     * Prepares the custom field data related to the object to pass to mform->set_data() and adds them to it
     *
     * This function must be called before calling $form->set_data($object);
     *
     * @param \stdClass $instance the instance that has custom fields, if 'id' attribute is present the custom
     *    fields for this instance will be added, otherwise the default values will be added.
     */
    public function instance_form_before_set_data(\stdClass $instance) {
        $instance->{$this->get_form_element_name()} = unserialize($this->get_value());
    }
}
