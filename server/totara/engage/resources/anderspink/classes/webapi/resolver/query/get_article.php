<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package engage_article
 */

namespace engage_anderspink\webapi\resolver\query;

use coding_exception;
use context_user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core\webapi\resolver\has_middleware;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use engage_anderspink\totara_engage\resource\anderspink_articles as model;
use totara_engage\access\access_manager;
use totara_engage\entity\engage_bookmark;
use totara_engage\entity\share;
use totara_engage\timeview\time_view;
use totara_reaction\loader\reaction_loader;
use totara_topic\provider\topic_provider;

final class get_article implements query_resolver, has_middleware
{
    /**
     * @param array $args
     * @param execution_context $ec
     *
     * @return array
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): array
    {
        global $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(context_user::instance($USER->id));
        }

        try {
            /** @var anderspink_articles $article */
            $article = anderspink_articles::from_resource_id($args['id']);
        } catch (\dml_exception $e) {
            throw new coding_exception("No article found");
        }

        if (!access_manager::can_access($article, $USER->id)) {
            throw new coding_exception("User with id '{$USER->id}' does not have access to this article");
        }

        $anderspinkArticle                  = $article->to_array();
        $anderspinkArticle['resource']      = anderspink_articles::from_instance($article->get_instanceid(),
            $article->get_resourcetype());
        $anderspinkArticle['topics']        = topic_provider::get_for_item($article->get_id(), 'engage_anderspink',
            'engage_resource');
        $anderspinkArticle['updateable']    = false;
        $anderspinkArticle['sharedbycount'] = share::repository()->get_total_recipients($article->get_id(), $article->get_resourcetype());
        $anderspinkArticle['owned']         = $USER->id == $article->get_userid();
        $anderspinkArticle['bookmarked']    = engage_bookmark::repository()->is_bookmarked($USER->id,
            $article->get_id(), $article::get_resource_type());
        $anderspinkArticle['image']         = $article->image;
        $anderspinkArticle['reacted']       = reaction_loader::exist($article->get_id(), model::get_resource_type(),
            'media', $USER->id);
        $anderspinkArticle['timeview']      = time_view::get_code($anderspinkArticle['timeview']);

        return $anderspinkArticle;
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