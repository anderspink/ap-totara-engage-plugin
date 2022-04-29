<?php

use totara_core\advanced_feature;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Class block_workspaces_edit_form
 */
class block_workspaces_edit_form extends block_edit_form
{
    /**
     * Add the custom config options
     *
     * @param MoodleQuickForm $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform)
    {
        global $PAGE, $CFG;

        $PAGE->requires->js_call_amd('block_workspaces/edit_form', 'init');
        $mform->addElement('header', 'custom_config_header', get_string('customblocksettings', 'block'));

        $default_count = $CFG->block_workspaces_recctr ?? 3;
        $mform->addElement('select', 'config_noi', get_string('config:number_of_items', 'block_workspaces'), range(0,10));
        $mform->setType('config_noi', PARAM_INT);
        $mform->setDefault('config_noi', $default_count);
    }

    /**
     * @return bool
     */
    protected function has_common_settings(): bool
    {
        return advanced_feature::is_enabled('ml_recommender');
    }
}
