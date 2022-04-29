<?php

$watchers = [
    [
        'hookname' => '\totara_reportedcontent\hook\get_review_context',
        'callback' => ['\engage_anderspink\watcher\reportedcontent_watcher', 'get_content']
    ],
    [
        'hookname' => '\totara_reportedcontent\hook\remove_review_content',
        'callback' => ['\engage_anderspink\watcher\reportedcontent_watcher', 'delete_anderspink_articles']
    ],
    [
        'hookname' => '\editor_weka\hook\find_context',
        'callback' => ['\engage_anderspink\watcher\editor_weka_watcher', 'load_context']
    ],
    [
        'hookname' => '\totara_topic\hook\get_deleted_topic_usages',
        'callback' => ['\engage_anderspink\watcher\totara_topic_watcher', 'on_deleted_topic_get_usage']
    ],
    [
        'hookname' => '\editor_weka\hook\search_users_by_pattern',
        'callback' => ['\engage_anderspink\watcher\editor_weka_watcher', 'on_search_users']
    ]
];