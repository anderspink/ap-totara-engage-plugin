<?php

use container_anderspink\anderspink;
use container_anderspink\totara\menu\anderspinkworkspace;
use totara_core\advanced_feature;
use totara_tui\output\component;

require_once(__DIR__ . '/../../../config.php');
require_login();
advanced_feature::require('container_workspace');

global $CFG, $PAGE, $OUTPUT, $USER;

$categoryId = anderspink::get_default_category_id();
$context    = context_coursecat::instance($categoryId);

$PAGE->set_context($context);

if (!has_capability('container/anderspink:manage', $context, $USER->id)) {
    redirect(new moodle_url('/container/type/workspace/index.php'));
    return;
}

$PAGE->set_title(get_string('anderspink_api_settings', 'container_anderspink'));
$PAGE->set_pagelayout('legacynolayout');
$PAGE->set_url(new moodle_url('/container/type/anderspink/api_settings.php'));
$PAGE->set_totara_menu_selected(anderspinkworkspace::class);

$component = new component('anderspink/pages/ApiSettings');
$component->register($PAGE);

echo $OUTPUT->header();
echo $OUTPUT->render($component);
echo $OUTPUT->footer();