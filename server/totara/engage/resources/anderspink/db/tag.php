<?php
defined('MOODLE_INTERNAL') || die();

$tagareas = [
    [
        'itemtype' => 'engage_resource',           // This must be a name of the database table (without prefix)
        'component' => 'engage_anderspink',
        'showstandard' => \core_tag_tag::STANDARD_ONLY,
        'topic' => true
    ]
];