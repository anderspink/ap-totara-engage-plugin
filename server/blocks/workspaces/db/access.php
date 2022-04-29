<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'block/workspaces:myaddinstance' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
    ],
];