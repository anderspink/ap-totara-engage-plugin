<?php

namespace engage_anderspink\webapi\resolver\query;

use coding_exception;
use context_user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core\webapi\resolver\has_middleware;
use engage_anderspink\totara_engage\resource\anderspink_articles;

final class draft_item implements query_resolver, has_middleware
{
    /**
     * @param array $args
     * @param execution_context $ec
     * @return anderspink_articles
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): anderspink_articles
    {
        global $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(context_user::instance($USER->id));
        }

        $article = anderspink_articles::from_resource_id($args['resourceid']);

        if (!$article->can_update($USER->id)) {
            throw new coding_exception("User is not allow to update the article");
        }

        return $article;
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