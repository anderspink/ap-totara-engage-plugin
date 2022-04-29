<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'container_anderspink\task\cron_fetch_briefings_and_boards',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '*',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => 'container_anderspink\task\cron_sync_briefings',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '3',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => 'container_anderspink\task\cron_post_random_discussion',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '8',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => 'container_anderspink\task\cron_sync_articles_items_with_shared_recipients',
        'blocking'  => 0,
        'minute'    => '*/45',
        'hour'      => '*',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => 'container_anderspink\task\cron_delete_old_discussions',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '1',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ]
];