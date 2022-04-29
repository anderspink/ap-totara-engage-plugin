<?php

namespace engage_anderspink\local;

use coding_exception;
use context;
use DateTime;
use engage_anderspink\entity\anderspink_articles as anderspink_articles_entity;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use Exception;
use html_writer;
use moodle_exception;
use moodle_url;
use totara_comment\comment_helper;
use totara_engage\entity\engage_resource;
use totara_engage\share\manager;
use totara_engage\timeview\time_view;
use totara_reaction\reaction_helper;

final class helper
{
    private function __construct()
    {
    }

    /**
     * @param  context  $context
     *
     * @return array
     */
    public static function get_editor_options(context $context): array
    {
        global $CFG;

        $options = [
            'subdirs'      => 1,
            'maxbytes'     => $CFG->maxbytes,
            'maxfiles'     => -1,
            'changeformat' => 1,
            'context'      => $context,
            'trusttext'    => 0,
            'overflowdiv'  => 1,
        ];

        if (get_config('engageresource_article', 'allowxss')) {
            $options['allowxss'] = 1;
        }

        return $options;
    }

    /**
     * As the method is for purging, we do not need capability check.
     *
     * @param  anderspink_articles  $article
     *
     * @throws coding_exception
     */
    public static function purge_article(anderspink_articles $article): void
    {
        // Delete resource.
        engage_resource::repository()
                       ->find_or_fail($article->get_id())
                       ->delete();

        // Delete shares.
        manager::delete($article->get_id(), anderspink_articles::get_resource_type());

        // Deleting comments.
        comment_helper::purge_area_comments(anderspink_articles::get_resource_type(), 'comment', $article->get_id());

        // Deleting reaction from the article.
        reaction_helper::purge_area_reactions(anderspink_articles::get_resource_type(), 'media', $article->get_id());

        // Delete files.
        self::delete_files($article);

        // Delete the attached image file.
        $processor = image_processor::make($article->get_id(), $article->get_context_id());
        $processor->delete_existing_image();

        // Delete itself.
        anderspink_articles_entity::repository()
                                  ->find_or_fail($article->get_instanceid())
                                  ->delete();
    }

    /**
     * @param  anderspink_articles  $article
     *
     * @return bool
     */
    public static function delete_files(anderspink_articles $article): bool
    {
        $fs = get_file_storage();

        return $fs->delete_area_files($article->get_context_id(), anderspink_articles::get_resource_type(), false,
                                      $article->get_id());
    }

    /**
     * @param  int  $readingTime
     *
     * @return string
     * @throws coding_exception
     */
    public static function generate_read_time(int $readingTime): string
    {
        if ($readingTime < 5) {
            return time_view::get_value('LESS_THAN_FIVE');
        } elseif ($readingTime >= 5 || $readingTime <= 10) {
            return time_view::get_value('FIVE_TO_TEN');
        } elseif ($readingTime > 10) {
            return time_view::get_value('MORE_THAN_TEN');
        } else {
            return "1";
        }
    }

    /**
     * @param  object  $content
     * @param  object|null  $img
     * @param  string  $type
     *
     * @return string
     * @throws coding_exception
     * @throws Exception
     */
    public static function generate_content_html(object $content, object $img = null, string $type = 'content'): string
    {
        global $OUTPUT;

        $data = (object)[
            'content' => (object)[
                'name'       => $content->name ?? '',
                'summary'    => $content->summary,
                'url'        => $content->url,
                'visit_link' => get_string('visit_link', 'container_anderspink'),
                'hashtag'    => (!empty($content->briefing)) ? self::generate_hashtag($content->briefing) : '',
            ],
        ];

        if (!is_null($img)) {
            $data->img = html_writer::img($img->url, $img->alt, $img->attr ?? null);
        }

        if ($type === 'content') {
            $data->content->link_source    = get_string('link_source', 'container_anderspink', $content->domain);
            $data->content->link_published = get_string(
                'link_published',
                'container_anderspink',
                self::generate_human_timestamp($content->published)
            );

            return $OUTPUT->render_from_template('engage_anderspink/article_content', $data);
        }

        if ($type === 'discussion') {
            $data->content->link_source    = $content->domain;
            $data->content->link_published = self::generate_human_timestamp($content->published);
            $data->content->visit_link     = get_string('visit_resource', 'container_anderspink');

            return $OUTPUT->render_from_template('engage_anderspink/article_discussion', $data);
        }

        return '';
    }

    /**
     * @param  string  $date
     *
     * @return string
     * @throws Exception
     */
    private static function generate_human_timestamp(string $date): string
    {
        $str      = strtotime($date);
        $dateTime = new DateTime("@" . $str);

        return $dateTime->format('jS F Y');
    }

    /**
     * @param  int  $time
     *
     * @return string
     */
    public static function get_time_ago(int $time): string
    {
        $timeDifference = time() - $time;

        if ($timeDifference < 1) {
            return 'less than 1 second ago';
        }
        $condition = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60      => 'month',
            24 * 60 * 60           => 'day',
            60 * 60                => 'hour',
            60                     => 'minute',
            1                      => 'second',
        ];

        foreach ($condition as $secs => $str) {
            $d = $timeDifference / $secs;

            if ($d >= 1) {
                $t = round($d);

                return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }

        return '';
    }

    /**
     * @param  string  $name
     * @param  bool  $isLink
     *
     * @return string
     * @throws moodle_exception
     */
    public static function generate_hashtag(string $name, bool $isLink = true): string
    {
        $name = strtolower($name);
        $name = str_replace(' ', '', $name);

        if ($isLink) {
            return html_writer::link((new moodle_url('/totara/catalog/index.php', ['catalog_fts' => $name])), "#$name");
        }

        return "#$name";
    }
}