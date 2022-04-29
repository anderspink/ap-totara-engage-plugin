<?php

namespace engage_anderspink\webapi\resolver\query;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core\webapi\resolver\has_middleware;
use dml_exception;
use engage_anderspink\api_article;
use moodle_exception;

final class get_api_articles implements query_resolver, has_middleware
{
    /**
     * @param array $args
     * @param execution_context $ec
     *
     * @return array
     * @throws dml_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function resolve(array $args, execution_context $ec): array
    {
        if (empty($team = $args['teamid'])) {
            throw new coding_exception("Cannot fetch entry with no team id provided");
        }

        if (empty($typeId = $args['typeid'])) {
            throw new coding_exception("Cannot fetch entry with no type id");
        }

        if (empty($type = $args['type'])) {
            throw new coding_exception("Cannot fetch entry with no valid type");
        }

        if (empty($page = $args['page'])) {
            $page = 1;
        }

        return api_article::from_api($team, $typeId, $type, $page);
    }

    /**
     * @return array
     */
    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('engage_resources'),
        ];
    }
}