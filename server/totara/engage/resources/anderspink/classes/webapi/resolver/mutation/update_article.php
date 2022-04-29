<?php

namespace engage_anderspink\webapi\resolver\mutation;

use coding_exception;
use context_user;
use core\json_editor\helper\document_helper;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\resolver\has_middleware;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use engage_anderspink\totara_engage\resource\anderspink_articles as model;
use moodle_exception;
use totara_engage\access\access;
use totara_engage\entity\engage_bookmark;
use totara_engage\entity\share;
use totara_engage\exception\resource_exception;
use totara_engage\exception\share_exception;
use totara_engage\share\manager as share_manager;
use totara_engage\share\recipient\manager as recipient_manager;
use totara_engage\timeview\time_view;
use totara_engage\webapi\middleware\require_valid_recipients;
use totara_reaction\loader\reaction_loader;
use totara_topic\provider\topic_provider;

final class update_article implements mutation_resolver, has_middleware
{
    /**
     * @param array $args
     * @param execution_context $ec
     *
     * @return array
     *
     * @throws moodle_exception
     * @throws coding_exception
     * @throws resource_exception
     * @throws share_exception
     */
    public static function resolve(array $args, execution_context $ec): array
    {
        global $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(context_user::instance($USER->id));
        }

        $id           = $args['resourceid'];
        $article_data = [
            'draft_id' => $args['draft_id'] ?? null,
        ];

        /** @var anderspink_articles $article */
        $article = anderspink_articles::from_resource_id($id);

        if (isset($args['access'])) {
            // Format the string access into a proper value that machine can understand.
            $access = access::get_value($args['access']);

            if (access::is_restricted($access) && empty($args['shares'])) {
                throw resource_exception::create('update', anderspink_articles::get_resource_type());
            }
            if (access::is_public($access) && empty($args['topics'])) {
                throw resource_exception::create('update', anderspink_articles::get_resource_type());
            }

            $article_data['access'] = $access;
        }

        // Default to the current format value of the article.
        $article_data['format'] = $article->get_format();

        if (isset($args['format'])) {
            $article_data['format'] = $args['format'];
        }

        if (isset($args['timeview'])) {
            $timeview                 = time_view::get_value($args['timeview']);
            $article_data['timeview'] = $timeview;
        }

        if (isset($args['name'])) {
            $article_data['name'] = $args['name'];
        }

        if (isset($args['content'])) {
            $content = $args['content'];
            $format  = $article_data['format'];

            if ((FORMAT_JSON_EDITOR == $format && document_helper::is_document_empty($content)) || empty($content)) {
                throw resource_exception::create(
                    'update',
                    anderspink_articles::get_resource_type(),
                    null,
                    "Article content is empty"
                );
            }

            $article_data['content'] = $content;
        }

        $article->update($article_data, $USER->id);

        // Add/remove topics.
        if (!empty($args['topics'])) {
            // Remove all the current topics first, but only if it is not appearing in this list.
            $article->remove_topics_by_ids($args['topics']);
            $article->add_topics_by_ids($args['topics']);
        }

        // Shares
        if (!empty($args['shares'])) {
            $recipients = recipient_manager::create_from_array($args['shares']);
            share_manager::share($article, anderspink_articles::get_resource_type(), $recipients);
        }

        $resource                  = $article->to_array();
        $resource['id']            = $article->get_instanceid();
        $resource['resource']      = anderspink_articles::from_instance($article->get_instanceid(),
            $article->get_resourcetype());
        $resource['topics']        = topic_provider::get_for_item($article->get_id(), 'engage_anderspink',
            'engage_resource');
        $resource['content']       = $article->get_content();
        $resource['image']         = $resource['resource']->image;
        $resource['format']        = $article->get_format();
        $resource['updateable']    = false;
        $resource['owned']         = $USER->id == $article->get_userid();
        $resource['timeview']      = time_view::get_code($resource['timeview']);
        $resource['sharedbycount'] = share::repository()->get_total_recipients($article->get_id(),
            $article->get_resourcetype());
        $resource['bookmarked']    = engage_bookmark::repository()->is_bookmarked($USER->id,
            $article->get_id(), $article::get_resource_type());
        $resource['reacted']       = reaction_loader::exist($article->get_id(), model::get_resource_type(),
            'media', $USER->id);

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('engage_resources'),
            new require_valid_recipients('shares'),
        ];
    }

}