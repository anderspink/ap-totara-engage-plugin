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
use totara_engage\exception\resource_exception;

final class add_workspace_briefing_bridge implements mutation_resolver, has_middleware
{

    /**
     * @param array $args
     * @param execution_context $ec
     * @return object
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function resolve(array $args, execution_context $ec): object
    {
        if (empty($workspaceId = $args['workspaceid'])) {
            throw new coding_exception("Cannot link to unknown workspace");
        }

        if (empty($teamId = $args['teamid'])) {
            throw new coding_exception("Cannot link with unknown API entry");
        }

        if (empty($bridgeType = $args['bridgetype'])) {
            throw new coding_exception("Type of a connection is not provided");
        }

        if (empty($bridgeId = $args['bridgeid'])) {
            throw new coding_exception("Resource id is not provided");
        }

        return anderspink_helper::create_bridge_connection($workspaceId, $teamId, $bridgeType, $bridgeId);
    }

    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
        ];
    }

}