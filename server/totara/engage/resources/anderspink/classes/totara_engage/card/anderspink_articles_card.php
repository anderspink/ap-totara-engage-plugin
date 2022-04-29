<?php

namespace engage_anderspink\totara_engage\card;

use coding_exception;
use engage_anderspink\local\helper;
use engage_anderspink\theme\file\anderspink_articles_image;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use moodle_exception;
use moodle_url;
use totara_comment\loader\comment_loader;
use totara_engage\card\card;
use totara_engage\entity\share;
use totara_engage\timeview\time_view;
use totara_reaction\loader\reaction_loader;
use totara_topic\provider\topic_provider;
use totara_tui\local\theme_config;
use totara_tui\output\component;

final class anderspink_articles_card extends card
{
    /**
     * @return component
     */
    public function get_tui_component(): component
    {
        return new component("anderspink/components/card/ArticleCard");
    }

    /**
     * @return array
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_extra_data($theme_config = null): array
    {
        $anderspink    = anderspink_articles::from_resource_id($this->instanceid);
        $article_image = new anderspink_articles_image();
        $image         = $article_image->get_default_url();

        $extra_data = [
            'image'     => $anderspink->get_attribute('image') ?? $image->out(false),
            'usage'     => anderspink_articles::get_resource_usage($this->instanceid),
            'timeview'  => null,
            'alt'       => '',
            'domain'    => $anderspink->get_attribute('domain'),
            'published' => helper::get_time_ago(strtotime($anderspink->get_attribute('date_published'))),
        ];

        $extra = $this->get_json_decoded_extra();

        if (isset($extra['timeview'])) {
            $extra_data['timeview'] = time_view::get_code($extra['timeview']);
        }

        $extra_data['alt'] = !empty($extra['alt_text']) ? $extra['alt_text'] : '';
        return $extra_data;
    }

    /**
     * @return array
     */
    public function get_topics(): array
    {
        $id = $this->get_instanceid();
        return topic_provider::get_for_item($id, $this->component, 'engage_resource');
    }

    /**
     * @return int
     */
    public function get_total_reactions(): int
    {
        $paginator = reaction_loader::get_paginator('engage_anderspink', 'media', $this->instanceid);
        return $paginator->get_total();
    }

    /**
     * @return int
     */
    public function get_total_comments(): int
    {
        return comment_loader::count_comments(
            $this->instanceid,
            'engage_anderspink',
            'comment'
        );
    }

    /**
     * @return int
     * @throws coding_exception
     */
    public function get_sharedbycount(): int
    {
        $anderspink = anderspink_articles::from_resource_id($this->instanceid, 'engage_anderspink');

        return share::repository()->get_total_recipients($anderspink->get_id(), $anderspink->get_resourcetype());
    }

    /**
     * @param string|null $preview_mode
     * @return moodle_url|null
     * @throws moodle_exception
     */
    public function get_card_image(?string $preview_mode = null, $theme_config = null): ?moodle_url
    {
        global $PAGE;

        $article_image = new anderspink_articles_image();
        $image         = $article_image->get_default_url();
        $anderspink    = anderspink_articles::from_resource_id($this->instanceid);

        if (!empty($anderspink->get_attribute('image'))) {
            $image = new moodle_url($anderspink->get_attribute('image'), ['theme' => $PAGE->theme->name]);
        }

        if ($preview_mode) {
            $image->param('preview', $preview_mode);
        }

        return $image;
    }

    /**
     * @return component
     */
    public function get_card_image_component(): component
    {
        return new component('engage_article/components/card/ArticleCardImage');
    }
}