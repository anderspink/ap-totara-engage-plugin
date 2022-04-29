<?php

namespace engage_anderspink\observer;

use coding_exception;
use dml_transaction_exception;
use engage_anderspink\event\anderspink_articles_updated;
use engage_anderspink\event\anderspink_articles_viewed;
use engage_anderspink\local\seen_recommended_item;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_core\content\content_handler;
use totara_engage\resource\resource_completion;

/**
 * Observer for article component
 */
final class anderspink_articles_observer
{
    /**
     * @param $event
     *
     * @return void
     * @throws coding_exception
     */
    public static function on_created($event): void
    {
        $resource_id = $event->get_item_id();
        $actor_id    = $event->get_user_id();

        $article = anderspink_articles::from_resource_id($resource_id);
        anderspink_articles_observer::handle_article($article, $actor_id);
    }

    /**
     * @param  anderspink_articles_updated  $event
     *
     * @return void
     * @throws coding_exception
     */
    public static function on_updated($event): void
    {
        $resource_id = $event->get_item_id();
        $actor_id    = $event->get_user_id();

        $article = anderspink_articles::from_resource_id($resource_id);
        anderspink_articles_observer::handle_article($article, $actor_id);
    }

    /**
     * Pass content through content handlers
     *
     * @param $article
     * @param  int  $actor_id
     *
     * @return void
     */
    private static function handle_article($article, int $actor_id): void
    {
        $handler = content_handler::create();

        // Note that we trust the owner of the article is the responsible one to trigger
        // the whole process of content handler.
        $handler->handle_with_params(
            $article->get_name(),
            $article->get_content(),
            $article->get_format(),
            $article->get_id(),
            'engage_article',
            anderspink_articles::CONTENT_AREA,
            $article->get_context()->id,
            $article->get_url(),
            $actor_id
        );
    }

    /**
     * @param $event
     *
     * @return void
     * @throws dml_transaction_exception
     * @throws coding_exception
     */
    public static function on_view_created($event): void
    {
        global $DB;

        $actor_id    = $event->get_user_id();
        $others      = $event->other;
        $owner_id    = $others['owner_id'];
        $resource_id = $event->get_item_id();

        $instance = resource_completion::instance($resource_id, $owner_id);

        $transaction = $DB->start_delegated_transaction();
        if ($instance->can_create($actor_id)) {
            $instance->create();
        }
        $transaction->allow_commit();

        // Flag article as seen if it is on users recommendations list.
        $data = $event->get_data();

        //Unset values created by base::create() method
        unset($data['eventname']);
        unset($data['component']);
        unset($data['action']);
        unset($data['target']);
        unset($data['objecttable']);
        unset($data['crud']);
        unset($data['edulevel']);
        unset($data['contextlevel']);
        unset($data['contextinstanceid']);
        unset($data['timecreated']);

        $article = anderspink_articles_viewed::create($data);
        seen_recommended_item::process_seen_event(
            (int)$event->userid,
            (int)$event->other['resourceid'],
            $event->component
        );

        // Clear instance.
        unset($instance);
    }
}