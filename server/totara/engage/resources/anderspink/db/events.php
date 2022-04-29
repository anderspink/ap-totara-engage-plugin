<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$observers = [
    [
        'eventname' => \engage_anderspink\event\anderspink_articles_created::class,
        'callback'  => 'engage_anderspink\totara_catalog\anderspink_articles::object_update_observer',
    ],
    [
        'eventname' => \engage_anderspink\event\anderspink_articles_updated::class,
        'callback'  => 'engage_anderspink\totara_catalog\anderspink_articles::object_update_observer',
    ],
    [
        'eventname' => \engage_anderspink\event\anderspink_articles_deleted::class,
        'callback'  => 'engage_anderspink\totara_catalog\anderspink_articles::object_update_observer',
    ],
    [
        'eventname' => '\engage_anderspink\event\tag_added',
        'callback'  => 'engage_anderspink\totara_catalog\anderspink_articles::object_update_observer',
    ],
    [
        'eventname' => '\engage_anderspink\event\tag_removed',
        'callback'  => 'engage_anderspink\totara_catalog\anderspink_articles::object_update_observer',
    ],
    [
        'eventname' => '\engage_anderspink\event\tag_updated',
        'callback'  => 'engage_anderspink\totara_catalog\anderspink_articles::object_update_observer',
    ],
    [
        'eventname' => \engage_anderspink\event\anderspink_articles_created::class,
        'callback'  => [\engage_anderspink\observer\anderspink_articles_observer::class, 'on_created'],
    ],
    [
        'eventname' => \engage_anderspink\event\anderspink_articles_updated::class,
        'callback'  => [\engage_anderspink\observer\anderspink_articles_observer::class, 'on_updated'],
    ],
    [
        'eventname' => \totara_comment\event\comment_created::class,
        'callback'  => [\engage_anderspink\observer\comment_observer::class, 'on_comment_created'],
    ],
    [
        'eventname' => \totara_comment\event\reply_created::class,
        'callback'  => [\engage_anderspink\observer\comment_observer::class, 'on_reply_created'],
    ],
    [
        'eventname' => \totara_comment\event\comment_updated::class,
        'callback'  => [\engage_anderspink\observer\comment_observer::class, 'on_comment_updated'],
    ],
    [
        'eventname' => '\totara_reaction\event\reaction_created',
        'callback'  => ['engage_anderspink\observer\reaction_observer', 'on_reaction_created'],
    ],
    [
        'eventname' => '\engage_anderspink\event\anderspink_articles_viewed',
        'callback'  => ['engage_anderspink\observer\anderspink_articles_observer', 'on_view_created'],
    ],
    [
        'eventname' => \core\event\user_deleted::class,
        'callback'  => [\engage_anderspink\totara_catalog\anderspink_articles::class, 'object_update_observer'],
    ],
];
