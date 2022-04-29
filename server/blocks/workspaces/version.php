<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version      = 2021050601;       // The current module version (Date: YYYYMMDDXX).
$plugin->requires     = 2017111309;       // Requires this Moodle version.
$plugin->component    = 'block_workspaces';
$plugin->dependencies = [
    'block_totara_recommendations' => 2020100100,
    'block_totara_recently_viewed' => 2020100100,
    'container_workspace'          => 2020100108,
];
