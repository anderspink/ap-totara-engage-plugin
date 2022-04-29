<?php
namespace engage_anderspink\watcher;

use coding_exception;
use core\entity\user;
use dml_exception;
use editor_weka\hook\find_context;
use editor_weka\hook\search_users_by_pattern;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use context;
use context_user;
use totara_engage\engage_core;
use totara_engage\loader\user_loader;
use totara_engage\query\user_query;
use totara_comment\comment;
use totara_comment\comment_helper;

/**
 * A watcher to load the context for editor weka.
 */
final class editor_weka_watcher {
    /**
     * @param find_context $hook
     * @return void
     * @throws dml_exception
     */
    public static function load_context(find_context $hook): void {
        global $DB, $USER;

        $component = $hook->get_component();
        $article_component = anderspink_articles::get_resource_type();

        if ($article_component !== $component) {
            return;
        }

        $area = $hook->get_area();
        if (anderspink_articles::CONTENT_AREA === $area) {
            $resource_id = $hook->get_instance_id();
            if (empty($resource_id)) {
                // Resource id is empty, then most likely this is for creating new instance, therefore we
                // will try to use the user in session context.
                $context = context_user::instance($USER->id);
                $hook->set_context($context);

                return;
            }

            $user_id = $DB->get_field('engage_resource', 'userid', ['id' => $resource_id]);

            $context = context_user::instance($user_id);
            $hook->set_context($context);
        }
    }

    /**
     * @param search_users_by_pattern $hook
     * @return void
     * @throws coding_exception
     */
    public static function on_search_users(search_users_by_pattern $hook): void {
        static::on_search_users_for_article($hook);
        static::on_search_users_for_comment($hook);
    }

    /**
     * Searching the users when we are in article section.
     *
     * @param search_users_by_pattern $hook
     * @return void
     * @throws coding_exception
     */
    private static function on_search_users_for_article(search_users_by_pattern $hook): void {
        if ($hook->is_db_run()) {
            return;
        }

        $component = $hook->get_component();
        if (article::get_resource_type() != $component) {
            return;
        }

        $context = context::instance_by_id($hook->get_context_id());
        $users = static::search_for_users(
            $context,
            $hook->get_actor_id(),
            $hook->get_pattern()
        );

        $hook->add_users($users);
        $hook->mark_db_run();
    }

    /**
     * Searching for users when we are in comment section.
     *
     * @param search_users_by_pattern $hook
     * @return void
     * @throws coding_exception
     */
    private static function on_search_users_for_comment(search_users_by_pattern $hook): void {
        if ($hook->is_db_run()) {
            return;
        }

        $component = $hook->get_component();
        if (comment::get_component_name() !== $component) {
            return;
        }

        $comment_id = $hook->get_instance_id();
        if (empty($comment_id)) {
            // Skip for comment.
            return;
        }

        $context_id = $hook->get_context_id();
        $context = context::instance_by_id($context_id);

        if (CONTEXT_USER != $context->contextlevel) {
            // We will skip this search.
            return;
        }

        comment_helper::validate_comment_area($hook->get_area());
        $comment = comment::from_id($comment_id);

        if (anderspink_articles::get_resource_type() !== $comment->get_component()) {
            return;
        }

        // Now this comment is for this article - we will start find users.
        $users = static::search_for_users(
            $context,
            $hook->get_actor_id(),
            $hook->get_pattern()
        );

        $hook->add_users($users);
        $hook->mark_db_run();
    }

    /**
     * @param context   $context
     * @param int       $actor_id
     * @param string    $pattern
     *
     * @return user[]
     */
    private static function search_for_users(context $context, int $actor_id, string $pattern): array {
        if (!engage_core::allow_access_with_tenant_check($context, $actor_id)) {
            return [];
        }

        $query = user_query::create_with_exclude_guest_user($context->id);
        $query->set_search_term($pattern);

        $result = user_loader::get_users($query);
        return $result->get_items()->all();
    }
}