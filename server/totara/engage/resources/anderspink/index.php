<?php

use core\notification;
use engage_anderspink\event\anderspink_articles_viewed;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_core\advanced_feature;
use totara_engage\access\access_manager;
use totara_playlist\totara_engage\link\nav_helper;
use totara_tui\output\component;

require_once(__DIR__ . "/../../../../config.php");
global $OUTPUT, $PAGE, $USER;

require_login();
advanced_feature::require('engage_resources');

// {ttr_engage_resource}'s id
$id     = required_param("id", PARAM_INT);
$source = optional_param('source', null, PARAM_TEXT);

/** @var anderspink_articles $resource */
$resource = anderspink_articles::from_resource_id($id);
$url      = new \moodle_url("/totara/engage/resources/anderspink/index.php", ['id' => $id]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('legacynolayout');

$tui = null;
if (!$resource->is_available()) {
    $message = get_string('resource_unavailable', 'totara_engage');

    // Fallback to the context system.
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title($message);

    $tui = new component('totara_engage/pages/EngageUnavailableResource');
    $tui->register($PAGE);
} else {
    if (access_manager::can_access($resource, $USER->id)) {
        $PAGE->set_context($resource->get_context());
        $PAGE->set_title($resource->get_name());

        // Build the back button
        [$back_button, $navigation_buttons] = nav_helper::build_resource_nav_buttons($resource->get_id(),
            $resource->get_userid(), $source);

        $tui = new component(
            'anderspink/pages/AnderspinkArticleView',
            [
                'resource-id'        => $id,
                'title'              => $resource->get_name(),
                'back-button'        => $back_button,
                'navigation-buttons' => $navigation_buttons,
            ]
        );

        $tui->register($PAGE);

        $event = anderspink_articles_viewed::from_articles($resource);
        $event->trigger();
    } else {
        $PAGE->set_context(\context_system::instance());
    }
}

echo $OUTPUT->header();

if (null !== $tui) {
    echo $OUTPUT->render($tui);
} else {
    notification::error(get_string('cannot_view_article', 'engage_anderspink'));
}

echo $OUTPUT->footer();