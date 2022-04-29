<?php

namespace engage_anderspink\totara_catalog\anderspink_article\filter_factory;

defined('MOODLE_INTERNAL') || die();

use lang_string;
use stdClass;
use totara_catalog\datasearch\equal;
use totara_catalog\datasearch\in_or_equal;
use totara_catalog\filter;
use totara_catalog\filter_factory;
use totara_catalog\merge_select\multi;
use totara_catalog\merge_select\tree;
use totara_engage\timeview\time_view as time;

class timeview extends filter_factory
{

    public static function get_filters(): array
    {
        $filters = [];

        // The panel filter can appear in the panel region.
        $paneldatafilter = new in_or_equal(
            'anderspink_articles_timeview_panel',
            'catalog',
            ['objectid', 'objecttype']
        );
        $paneldatafilter->add_source(
            'article.timeview',
            '{engage_anderspink}',
            'anderspink_article',
            ['objectid' => 'anderspink_article.id', 'objecttype' => "'engage_anderspink'"]
        );

        $panelselector = new multi(
            'anderspink_articles_timeview_panel',
            new lang_string('filter:timeview', 'engage_anderspink')
        );
        $panelselector->add_options_loader(self::get_multi_optionsloader());

        $filters[] = new filter(
            'anderspink_articles_timeview_multi',
            filter::REGION_PANEL,
            $paneldatafilter,
            $panelselector
        );

        // The browse filter can appear in the primary region.
        $browsedatafilter = new equal(
            'anderspink_articles_timeview_browse',
            'catalog',
            ['id']
        );
        $browsedatafilter->add_source(
            'anderspink_article.timeview',
            '{engage_anderspink}',
            'anderspink_article',
            ['id' => 'anderspink_article.id']
        );

        $browseselector = new tree(
            'anderspink_articles_timeview_browse',
            new lang_string('filter:timeview', 'engage_anderspink'),
            self::get_tree_optionsloader()
        );
        $browseselector->add_all_option();

        $filters[] = new filter(
            'anderspink_articles_timeview_tree',
            filter::REGION_BROWSE,
            $browsedatafilter,
            $browseselector
        );

        return $filters;
    }

    /**
     * @return callable
     */
    private static function get_tree_optionsloader(): callable
    {
        return function () {
            $items = [
                time::LESS_THAN_FIVE => get_string('filter:timeviewlow', 'engage_anderspink'),
                time::FIVE_TO_TEN    => get_string('filter:timeviewmed', 'engage_anderspink'),
                time::MORE_THAN_TEN  => get_string('filter:timeviewhigh', 'engage_anderspink'),
            ];

            $options = [];

            foreach ($items as $key => $name) {
                $option       = new stdClass();
                $option->key  = $key;
                $option->name = $name;
                $options[]    = $option;
            }

            return $options;
        };
    }

    /**
     * @return callable
     */
    private static function get_multi_optionsloader(): callable
    {
        return function () {
            $options = [
                time::LESS_THAN_FIVE => get_string('filter:timeviewlow', 'engage_anderspink'),
                time::FIVE_TO_TEN    => get_string('filter:timeviewmed', 'engage_anderspink'),
                time::MORE_THAN_TEN  => get_string('filter:timeviewhigh', 'engage_anderspink'),
            ];

            return $options;
        };
    }
}
