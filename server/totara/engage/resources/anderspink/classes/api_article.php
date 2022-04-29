<?php

namespace engage_anderspink;

use container_anderspink\entity\anderspink_api;
use container_anderspink\local\AnderspinkApiClient;
use dml_exception;
use moodle_exception;
use moodle_url;
use stdClass;

final class api_article
{

    public int    $id;
    public int    $api_id;
    public int    $team;
    public string $title;
    public string $content;
    public string $image;
    public string $date_published;
    public string $url;
    public string $author;
    public string $domain;
    public int    $reading_time;

    /**
     * @param  int  $team
     * @param  int  $typeId
     * @param  string  $type
     * @param  int  $page
     *
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function from_api(int $team, int $typeId, string $type, int $page): array
    {
        global $DB;

        /** @var anderspink_api $teamKey */
        $teamKey          = anderspink_api::repository()->find_or_fail($team);
        $anderspinkClient = new AnderspinkApiClient($teamKey->api_key);
        $articles         = $anderspinkClient->fetchArticles($typeId, $type, $page);
        $return           = [];

        foreach ($articles->articles as $article) {
            if (strtotime($article->date_published) > time()) {
                continue;
            }

            $anderspinkArticle = new self();
            $anderspinkArticle->map_record($article, true);

            $return[] = $anderspinkArticle;
        }

        return $return;
    }

    /**
     * @param  stdClass  $record
     * @param  bool  $fromApi
     *
     * @throws moodle_exception
     */
    protected function map_record(stdClass $record, bool $fromApi = false): void
    {
        if ($fromApi) {
            if (property_exists($record, 'id')) {
                $this->api_id = $record->id;
                $this->id     = 0;
            }
        } else {
            if (property_exists($record, 'id')) {
                $this->id = $record->id;
            }

            if (property_exists($record, 'apiid')) {
                $this->api_id = $record->api_id;
            }
        }

        if (property_exists($record, 'team')) {
            $this->team = $record->team;
        }

        if (property_exists($record, 'title')) {
            $this->title = (string)$record->title;
        }

        if (property_exists($record, 'content')) {
            $this->content = $record->content ?? '';
        }

        if (property_exists($record, 'image')) {
            $this->image = $record->image ?? '/' . (new moodle_url('theme/image.php/ventura/engage_article/0/default', ['preview' => 'engage_article_resource']))->out(false);
        }

        if (property_exists($record, 'date_published')) {
            $this->date_published = $record->date_published;
        }

        if (property_exists($record, 'url')) {
            $this->url = $record->url;
        }

        if (property_exists($record, 'author')) {
            $this->author = $record->author ?? '';
        }

        if (property_exists($record, 'domain')) {
            $this->domain = $record->domain ?? '';
        }

        if (property_exists($record, 'reading_time')) {
            $this->reading_time = $record->reading_time ?? 0;
        }
    }
}