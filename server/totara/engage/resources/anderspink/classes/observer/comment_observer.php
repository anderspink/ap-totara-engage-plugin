<?php

namespace engage_anderspink\observer;

use coding_exception;
use core\task\manager;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_comment\comment;
use totara_comment\event\comment_created;
use totara_comment\event\comment_updated;
use totara_comment\event\reply_created;
use totara_core\content\content_handler;
use totara_engage\task\comment_notify_task;

/**
 * Observer for comment component
 */
final class comment_observer
{
    private function __construct()
    {
    }

    /**
     * @param comment_created $event
     * @return void
     * @throws coding_exception
     */
    public static function on_comment_created(comment_created $event): void
    {
        $record  = $event->get_record_snapshot(comment::get_entity_table(), $event->objectid);
        $comment = comment::from_record($record);
        comment_observer::handle_comment($comment, $event->get_user_id());
    }

    /**
     * @param comment_updated $event
     * @return void
     * @throws coding_exception
     */
    public static function on_comment_updated(comment_updated $event): void
    {
        $record  = $event->get_record_snapshot(comment::get_entity_table(), $event->objectid);
        $comment = comment::from_record($record);
        comment_observer::handle_comment($comment, $event->userid);
    }

    /**
     * @param reply_created $event
     * @return void
     * @throws coding_exception
     */
    public static function on_reply_created(reply_created $event): void
    {
        $record = $event->get_record_snapshot(comment::get_entity_table(), $event->objectid);
        $reply  = comment::from_record($record);

        comment_observer::handle_comment($reply, $event->userid);
    }

    /**
     * Pass comment through content handlers
     *
     * @param comment $comment
     * @param int|null $user_id
     *
     * @return void
     * @throws coding_exception
     */
    private static function handle_comment(comment $comment, ?int $user_id = null): void
    {
        $component = $comment->get_component();
        if (anderspink_articles::get_resource_type() !== $component) {
            return;
        }

        $area = $comment->get_area();
        if ('comment' === $area) {
            $resource_id = $comment->get_instanceid();
            $resource    = anderspink_articles::from_resource_id($resource_id);

            $handler = content_handler::create();
            $handler->handle_with_params(
                $resource->get_name(),
                $comment->get_content(),
                $comment->get_format(),
                $comment->get_id(),
                $comment->get_component(),
                $comment->get_area(),
                $resource->get_context()->id,
                $resource->get_url(),
                $user_id
            );

            self::create_owner_notification_task($comment, $resource);
        }
    }

    /**
     * @param comment $comment
     * @param anderspink_articles $article
     * @return void
     * @throws coding_exception
     */
    protected static function create_owner_notification_task(comment $comment, anderspink_articles $article): void
    {
        // If commenter is not owner, task will be initialized.
        if ($comment->get_userid() !== $article->get_userid()) {
            $task = new comment_notify_task();
            $task->set_custom_data([
                'url'          => $article->get_url(),
                'owner'        => $article->get_userid(),
                // As article is part of engage resource, adhoc task will be triggered in the engage and we do not want
                // to set message setting for engage_article.
                'component'    => 'totara_engage',
                'resourcetype' => get_string('message_resource', 'totara_engage'),
                'commenter'    => $comment->get_userid(),
                'name'         => $article->get_name(),
                'is_comment'   => !$comment->is_reply(),
            ]);

            manager::queue_adhoc_task($task);
        }
    }
}