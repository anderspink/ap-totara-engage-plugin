<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Manage
    'container/anderspink:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT
    ]
];