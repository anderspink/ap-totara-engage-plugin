<?php

namespace container_anderspink\webapi\resolver\mutation;

use coding_exception;
use container_anderspink\local\anderspink_helper;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\resolver\has_middleware;
use dml_exception;

final class delete_api implements mutation_resolver, has_middleware
{

    /**
     * @param array $args
     * @param execution_context $ec
     * @return bool
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): bool
    {
        $apiId = $args['id'];

        if (empty($apiId) || $apiId < 1){
            throw new coding_exception("Cannot find a api with provided ID");
        }

        $result = anderspink_helper::delete_api($apiId);

        if (!$result) {
            throw new coding_exception("Could not delete an api key with id {$apiId}");
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
        ];
    }
}