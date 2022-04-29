<?php

namespace engage_anderspink\webapi\resolver\mutation;

use coding_exception;
use context_user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\resolver\has_middleware;
use engage_anderspink\totara_engage\resource\anderspink_articles;

/**
 * Mutation resolver for engage_article_delete
 */
final class delete_article implements mutation_resolver, has_middleware
{
    /**
     * @param array $args
     * @param execution_context $ec
     *
     * @return bool
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): bool
    {
        global $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(context_user::instance($USER->id));
        }

        $resource = anderspink_articles::from_resource_id($args['id']);
        $resource->remove_topics_by_ids();
        $resource->delete($USER->id);

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('engage_resources'),
        ];
    }

}