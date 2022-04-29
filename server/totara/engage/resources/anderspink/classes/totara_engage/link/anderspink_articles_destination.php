<?php

namespace engage_anderspink\totara_engage\link;

use engage_anderspink\totara_engage\resource\anderspink_articles;
use moodle_url;
use totara_engage\link\destination_generator;

final class anderspink_articles_destination extends destination_generator
{
    /**
     * @var array
     */
    protected $auto_populate = ['id'];

    /**
     * @return string
     * @throws \coding_exception
     */
    public function label(): string
    {
        $article = anderspink_articles::from_resource_id($this->attributes['id']);
        return get_string('back_button', 'engage_anderspink', $article->get_name());
    }

    /**
     * @return moodle_url
     */
    protected function base_url(): moodle_url
    {
        return new moodle_url('/totara/engage/resources/anderspink/index.php');
    }
}