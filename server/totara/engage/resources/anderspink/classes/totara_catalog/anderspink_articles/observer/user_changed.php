<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\observer;

use core\orm\query\builder;
use dml_exception;
use engage_anderspink\totara_engage\resource\anderspink_articles as resource_article;
use totara_catalog\observer\object_update_observer;

final class user_changed extends object_update_observer
{
    /**
     * @return array
     */
    public function get_observer_events(): array
    {
        return [
            '\core\event\user_deleted',
        ];
    }

    /**
     * @return void
     * @throws dml_exception
     */
    protected function init_change_objects(): void
    {
        global $DB;

        $user_id = $this->event->objectid;

        // Fetch all the articles to be deleted from catalog.
        $articles= builder::table('engage_anderspink_articles', 'ea')
            ->join(['engage_resource', 'er'], 'er.instanceid', 'ea.id')
            ->where('er.resourcetype', resource_article::get_resource_type())
            ->where('user_id', $user_id)
            ->get();

        foreach ($articles as $article) {
            $this->register_for_delete($article->id);
        }
    }
}