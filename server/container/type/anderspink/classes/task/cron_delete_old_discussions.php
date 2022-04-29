<?php

namespace container_anderspink\task;

use coding_exception;
use container_anderspink\bridge;
use container_anderspink\entity\anderspink_discussion_index;
use core\orm\query\builder;
use core\task\scheduled_task;
use dml_transaction_exception;
use lang_string;
use Throwable;

class cron_delete_old_discussions extends scheduled_task
{
    /**
     * @return string
     * @throws coding_exception
     */
    public function get_name(): string
    {
        return get_string('crontask_delete_old_discussions', 'container_anderspink');
    }

    /**
     * - Get all indexed discussion bigger or eq to 7 days
     * - Foreach discussion check if a record in workspace table exists,
     *      - IF it does not remove index.
     * - Check for discussion reactions and comments,
     *      - IF returns true skip record
     *      - ELSE delete workspace record and index.
     *
     * @return void
     * @throws Throwable
     * @throws coding_exception
     * @throws dml_transaction_exception
     */
    public function execute()
    {
        global $DB;

        $now         = time();
        $sevenDayAgo = $now - (60 * 60 * 24 * 7);

        $discussions = anderspink_discussion_index::repository()->where('timecreated', '<=', $sevenDayAgo);

        if (empty($discussions) || !$discussions->exists()) {
            return;
        }

        $discussions = $discussions->get();
        $transaction = $DB->start_delegated_transaction();

        try {
            /** @var anderspink_discussion_index $discussion */
            foreach ($discussions as $discussion) {
                $workspaceDiscussion = builder::table('workspace_discussion')->where('id', $discussion->discussion);

                if (empty($workspaceDiscussion) || !$workspaceDiscussion->exists()) {
                    $discussion->delete();
                    continue;
                }

                $workspaceDiscussionRecord = $workspaceDiscussion->one();
                $checkForInteractions      = bridge::check_for_interactions($workspaceDiscussionRecord->id, [
                    'reaction' => 'discussion',
                    'comment'  => 'discussion',
                ],'container_workspace');

                if ($checkForInteractions) {
                    continue;
                }

                $workspaceDiscussion->delete();
                $discussion->delete();
            }
        } catch (\Exception $e) {
            $DB->rollback_delegated_transaction($transaction);
        }

        $DB->commit_delegated_transaction($transaction);
    }
}