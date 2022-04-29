<?php

use container_anderspink\event\anderspink_bridge_created;
use container_anderspink\event\anderspink_bridge_delete_old_content;
use container_workspace\event\workspace_deleted;

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => anderspink_bridge_created::class,
        'callback'  => '\container_anderspink\bridge::bridge_created',
    ],
    [
        'eventname' => workspace_deleted::class,
        'callback'  => '\container_anderspink\bridge::workspace_deleted',
    ],
    [
        'eventname' => anderspink_bridge_delete_old_content::class,
        'callback' => '\container_anderspink\bridge::delete_old_content'
    ]
];