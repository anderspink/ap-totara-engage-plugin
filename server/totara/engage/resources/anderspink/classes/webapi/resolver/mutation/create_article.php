<?php

namespace engage_anderspink\webapi\resolver\mutation;

use coding_exception;
use container_anderspink\briefing;
use context_user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\resolver\has_middleware;
use engage_anderspink\local\helper;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use engage_anderspink\totara_engage\resource\anderspink_articles as model;
use totara_engage\access\access;
use totara_engage\entity\engage_bookmark;
use totara_engage\entity\share;
use totara_engage\exception\resource_exception;
use totara_engage\timeview\time_view;
use totara_engage\webapi\middleware\require_valid_recipients;
use totara_reaction\loader\reaction_loader;
use totara_topic\provider\topic_provider;

/**
 * Mutation resolver for engage_article_create
 */
final class create_article implements mutation_resolver, has_middleware
{
    /**
     * @param  array  $args
     * @param  execution_context  $ec
     *
     * @return array
     * @throws coding_exception
     * @throws resource_exception
     * @throws \moodle_exception
     */
    public static function resolve(array $args, execution_context $ec): array
    {
        global $USER;
        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(context_user::instance($USER->id));
        }

        $article_data = [
            'name'   => $args['name'],
            'topics' => $args['topics'] ?? [],
            'shares' => $args['shares'] ?? [],
            'format' => FORMAT_HTML,
        ];

        if (isset($args['access']) && !is_numeric($args['access']) && is_string($args['access'])) {
            // Format the string access into a proper value that machine can understand.
            $article_data['access'] = access::get_value($args['access']);
        }

        $content       = json_decode($args['content']);
        $content->name = $article_data['name'];

        if (!empty($content->briefing)) {
            $briefingId        = explode('_', $content->briefing)[1];
            $content->briefing = (briefing::from_apiid((int) $briefingId))->name;
        }

        $article_data['image']          = $content->image;
        $article_data['url']            = $content->url;
        $article_data['domain']         = $content->domain;
        $article_data['date_published'] = $content->published;
        $article_data['author']         = $content->author;
        $article_data['team']           = $content->team;
        $article_data['api_id']         = $content->api;
        $article_data['timeview']       = helper::generate_read_time($content->reading_time / 60);

        if (strpos($content->image, 'image.php/ventura') === false) {
            $articleImg = (object) ['url' => $content->image, 'alt' => $args['name'], 'attr' => ['width' => '100%']];
        } else {
            $articleImg = null;
        }

        $article_data['content'] = helper::generate_content_html($content, $articleImg);

        /** @var anderspink_articles $resource */
        $resource                           = anderspink_articles::create($article_data, $USER->id);
        $anderspinkArticle                  = $resource->to_array();
        $anderspinkArticle['resource']      = anderspink_articles::from_instance($resource->get_instanceid(),
            $resource->get_resourcetype());
        $anderspinkArticle['topics']        = topic_provider::get_for_item($resource->get_id(), 'engage_anderspink',
            'engage_resource');
        $anderspinkArticle['updateable']    = false;
        $anderspinkArticle['sharedbycount'] = share::repository()->get_total_sharers($resource->get_id(),
            $resource::get_resource_type());
        $anderspinkArticle['owned']         = $USER->id == $resource->get_userid();
        $anderspinkArticle['bookmarked']    = engage_bookmark::repository()->is_bookmarked($USER->id,
            $resource->get_id(), $resource::get_resource_type());
        $anderspinkArticle['image']         = $content->image;
        $anderspinkArticle['reacted']       = reaction_loader::exist($resource->get_id(), model::get_resource_type(),
            'media', $USER->id);
        $anderspinkArticle['timeview']      = time_view::get_code($anderspinkArticle['timeview']);

        return $anderspinkArticle;
    }

    /**
     * @return array
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