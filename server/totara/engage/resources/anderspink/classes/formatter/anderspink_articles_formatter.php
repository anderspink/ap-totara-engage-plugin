<?php

namespace engage_anderspink\formatter;

use core\webapi\formatter\formatter;
use engage_anderspink\entity\anderspink_articles;
use stdClass;
use totara_engage\formatter\field\date_field_formatter;

/**
 * Formatter for article
 */
final class anderspink_articles_formatter extends formatter
{
    /**
     * article_formatter constructor.
     *
     * @param anderspink_articles $article
     */
    public function __construct(anderspink_articles $article)
    {
        $record = new stdClass();

        // Id here is the article's instanceid.
        $record->id = $article->get_instanceid();

        $record->content        = $article->get_content();
        $record->format         = $article->get_format();
        $record->title          = $article->get_title();
        $record->image          = $article->get_image();
        $record->date_published = $article->get_date_published();
        $record->url            = $article->get_url();
        $record->author         = $article->get_author();
        $record->domain         = $article->get_domain();
        $record->resourceid     = $article->get_id();
        $record->timecreated    = $article->get_timecreated();
        $record->timemodified   = $article->get_timemodified();

        parent::__construct($record, $article->get_context());
    }

    /**
     * @param string $field
     * @return mixed|null
     */
    protected function get_field(string $field)
    {
        if ('timedescription' === $field) {
            return parent::get_field('timecreated');
        }

        return parent::get_field($field);
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function has_field(string $field): bool
    {
        if ('timedescription' === $field) {
            return true;
        }

        return parent::has_field($field);
    }

    /**
     * @return array
     */
    protected function get_map(): array
    {
        $that = $this;

        return [
            'id'              => null,
            'content'         => null,
            'format'          => null,
            'title'           => null,
            'image'           => null,
            'date_published'  => null,
            'url'             => null,
            'author'          => null,
            'domain'          => null,
            'timedescription' => function (int $value, date_field_formatter $formatter) use ($that): string {
                if (null !== $that->object->timemodified && 0 !== $that->object->timemodified) {
                    $formatter->set_timemodified($that->object->timemodified);
                }

                return $formatter->format($value);
            },
        ];
    }
}