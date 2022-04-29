<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Manage
    'engage/anderspink:manage' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ],
];