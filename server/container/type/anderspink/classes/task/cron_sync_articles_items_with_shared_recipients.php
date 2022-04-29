<?php

namespace container_anderspink\task;

use coding_exception;
use container_anderspink\bridge;
use core\orm\query\builder;
use core\orm\query\exceptions\multiple_records_found_exception;
use core\orm\query\exceptions\record_not_found_exception;
use core\task\scheduled_task;
use dml_transaction_exception;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use lang_string;

class cron_sync_articles_items_with_shared_recipients extends scheduled_task
{
    /**
     * @return lang_string|string
     * @throws coding_exception
     */
    public function get_name()
    {
        return get_string('crontask_sync_articles_items_with_shared_recipients', 'container_anderspink');
    }


    /**
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     * @throws dml_transaction_exception
     */
    public function execute()
    {
        global $DB;

        $shares        = builder::table('engage_share')->where('component', 'engage_anderspink')->get();
        $notInteracted = [];

        foreach ($shares as $share) {
            $interactions = bridge::check_for_interactions($share->itemid, [
                'reaction' => anderspink_articles::REACTION_AREA,
                'comment'  => anderspink_articles::COMMENT_AREA,
            ]);

            if (!$interactions) {
                $notInteracted[] = $share;
            }
        }

        $shares      = $notInteracted;
        $transaction = $DB->start_delegated_transaction();

        foreach ($shares as $share) {
            $engageResource = builder::table('engage_resource')
                                     ->where('id', $share->itemid)
                                     ->where('resourcetype', 'engage_anderspink');

            if (empty($engageResource) || !$engageResource->exists()) {
                $this->deleteShares($share->id);
            } else {
                $engageResource     = $engageResource->one();
                $anderspinkResource = builder::table('engage_anderspink_articles')
                                             ->where('id', $engageResource->instanceid);

                if (empty($anderspinkResource) || !$anderspinkResource->exists()) {
                    builder::table('engage_resource')->where('id', $engageResource->id)->delete();

                    $this->deleteShares($share->id);
                }
            }
        }

        $DB->commit_delegated_transaction($transaction);
    }

    /**
     * @param  int  $shareId
     */
    private function deleteShares(int $shareId): void
    {
        builder::table('engage_share_recipient')
               ->where('shareid', $shareId)
               ->where('component', 'container_workspace')
               ->delete();

        builder::table('engage_share')->where('id', $shareId)->delete();
    }

}