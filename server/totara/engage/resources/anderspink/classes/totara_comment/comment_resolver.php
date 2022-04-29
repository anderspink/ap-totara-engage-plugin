<?php

namespace engage_anderspink\totara_comment;

use coding_exception;
use context_user;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_comment\comment;
use totara_comment\resolver;
use totara_engage\access\access_manager;

/**
 * Comment resolver for engage_article
 */
final class comment_resolver extends resolver
{
    /**
     * @param string $area
     * @return bool
     */
    private function is_valid_area(string $area): bool
    {
        return in_array($area, [anderspink_articles::COMMENT_AREA]);
    }

    /**
     * @param int $instanceid
     * @param string $area
     * @param int $actorid
     *
     * @return bool
     */
    public function is_allow_to_create(int $instanceid, string $area, int $actorid): bool
    {
        if (!$this->is_valid_area($area)) {
            return false;
        }

        // If user can access to the instance, meaning that user can create the comment.
        $article = anderspink_articles::from_resource_id($instanceid);
        return access_manager::can_access($article, $actorid);
    }

    /**
     * @param comment $comment
     * @param int $actorid
     *
     * @return bool
     */
    public function is_allow_to_update(comment $comment, int $actorid): bool
    {
        $owner_id = $comment->get_userid();

        return (access_manager::can_manage_engage(context_user::instance($owner_id),
                $actorid) || $actorid == $owner_id);
    }

    /**
     * @param int $resourceid
     * @param string $area
     * @return int
     */
    public function get_context_id(int $resourceid, string $area): int
    {
        $article = anderspink_articles::from_resource_id($resourceid);
        $context = $article->get_context();

        return $context->id;
    }

    /**
     * @param comment $comment
     * @param int $actorid
     *
     * @return bool
     */
    public function is_allow_to_delete(comment $comment, int $actorid): bool
    {
        $owner_id = $comment->get_userid();
        return (access_manager::can_manage_engage(context_user::instance($owner_id),
                $actorid) || $actorid == $owner_id);
    }

    /**
     * @param int $instance_id
     * @param string $area
     * @param int $actor_id
     *
     * @return bool
     * @throws coding_exception
     */
    public function can_see_comments(int $instance_id, string $area, int $actor_id): bool
    {
        if (!$this->is_valid_area($area)) {
            throw new coding_exception("Not supported area by component '{$this->component}'");
        }

        $article = anderspink_articles::from_resource_id($instance_id);
        return access_manager::can_access($article, $actor_id);
    }

    /**
     * @param comment $comment
     * @param int $actor_id
     *
     * @return bool
     * @throws coding_exception
     */
    public function can_view_reactions_of_comment(comment $comment, int $actor_id): bool
    {
        $area = $comment->get_area();

        if (!$this->is_valid_area($area)) {
            throw new coding_exception("Not supported area by component '{$this->component}'");
        }

        $instance_id = $comment->get_instanceid();
        $article     = anderspink_articles::from_resource_id($instance_id);

        return access_manager::can_access($article, $actor_id);
    }
}