<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2021112501;          // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2017111309;          // Requires this Totara version.
$plugin->component = 'engage_anderspink';

$plugin->dependencies = [
    'totara_topic'    => 2019112700,
    'totara_reaction' => 2019081200,
    'totara_comment'  => 2019101500,
    'editor_weka'     => 2019111800,
    'totara_playlist' => 2020031201,
    'engage_article'  => 2020100101,
];
