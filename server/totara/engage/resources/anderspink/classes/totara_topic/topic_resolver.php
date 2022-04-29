<?php

namespace engage_anderspink\totara_topic;

use context;
use context_user;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_topic\resolver\resolver as base;
use totara_topic\topic;

/**
 * Resolver for article's topic.
 */
final class topic_resolver extends base
{
    /**
     * @param topic $topic
     * @param int $itemid
     * @param int $actorid
     * @param string $itemtype
     *
     * @return bool
     */
    public function can_add_usage(topic $topic, int $itemid, string $itemtype, int $actorid): bool
    {
        if ($itemtype !== 'engage_resource') {
            debugging("Invalid itemtype '{$itemtype}'", DEBUG_DEVELOPER);
            return false;
        }

        $article = anderspink_articles::from_resource_id($itemid);

        if (!$article->can_update($actorid)) {
            return false;
        }

        return true;
    }

    /**
     * @param topic $topic
     * @param int $instanceid
     * @param int $actorid
     * @param string $itemtype
     *
     * @return bool
     */
    public function can_delete_usage(topic $topic, int $instanceid, string $itemtype, int $actorid): bool
    {
        if ($itemtype !== 'engage_resource') {
            debugging("Invalid itemtype '{$itemtype}'", DEBUG_DEVELOPER);
            return false;
        }

        $article = anderspink_articles::from_resource_id($instanceid);

        if (!$article->can_update($actorid)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $itemid
     * @param string $itemtype
     *
     * @return context
     */
    public function get_context_of_item(int $itemid, string $itemtype): context
    {
        $article = anderspink_articles::from_resource_id($itemid);
        $userid  = $article->get_userid();

        return context_user::instance($userid);
    }
}