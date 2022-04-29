<?php

namespace engage_anderspink\observer;

use core\task\manager;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\task\like_notify_task;
use totara_reaction\event\reaction_created;

/**
 * Observer for reaction component
 */
final class reaction_observer
{
    private function __construct()
    {
    }

    /**
     * @param reaction_created $event
     * @return void
     * @throws \coding_exception
     */
    public static function on_reaction_created(reaction_created $event): void
    {
        $others = $event->other;
        if ($others['component'] === anderspink_articles::get_resource_type()) {
            $liker_id = $event->userid;
            $article  = anderspink_articles::from_resource_id($others['instanceid']);

            if ($liker_id !== $article->get_userid()) {
                $task = new like_notify_task();
                $task->set_custom_data([
                    'url'          => $article->get_url(),
                    'liker'        => $liker_id,
                    'owner'        => $article->get_userid(),
                    'name'         => $article->get_name(),
                    'resourcetype' => get_string('message_resource', 'totara_engage'),
                ]);

                manager::queue_adhoc_task($task);
            }
        }
    }
}