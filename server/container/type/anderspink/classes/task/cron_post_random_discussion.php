<?php

namespace container_anderspink\task;

use coding_exception;
use container_anderspink\bridge;
use container_anderspink\entity\anderspink_bridge;
use container_workspace\exception\discussion_exception;
use container_workspace\workspace;
use core\orm\query\builder;
use core\task\scheduled_task;

class cron_post_random_discussion extends scheduled_task
{
    public function get_name()
    {
        return get_string('crontask_post_random_discussion', 'container_anderspink');
    }

    /**
     * @throws coding_exception
     * @throws discussion_exception
     */
    public function execute()
    {
        $records       = anderspink_bridge::repository()->get();
        $now           = time();
        $oneDayAgo     = $now - (60 * 60 * 24);
        $numberToPost  = 3;
        $lastWorkspace = 0;

        foreach ($records as $record) {
            if ($lastWorkspace === $record->workspace) {
                continue;
            }

            /** @var workspace $workspace */
            $workspace     = workspace::from_id($record->workspace);
            $lastWorkspace = $record->workspace;

            $workspaceContent = builder::table('engage_share_recipient', 'esr')
                                       ->join(['engage_share', 's'], 'esr.shareid', 's.id')
                                       ->join(['engage_anderspink_articles', 'eaa'], 's.itemid', 'eaa.id')
                                       ->select('eaa.id as id')
                                       ->where('esr.instanceid', $workspace->get_id())
                                       ->where('esr.component', 'container_workspace')
                                       ->where('s.timecreated', '>', $oneDayAgo)
                                       ->order_by_raw('RAND()')
                                       ->limit(10)
                                       ->get()
                                       ->to_array();

            // If less than $numberToPost then we post what we can
            if (sizeof($workspaceContent) < $numberToPost) {
                $this->postDiscussion($workspace, $workspaceContent);
                continue;
            }

            $randoms = array_rand($workspaceContent, $numberToPost);

            //To ensure we have only 3
            if (sizeof($randoms) > $numberToPost) {
                $randoms = array_slice($randoms, 0, 2);
            }

            $this->postDiscussion($workspace, $workspaceContent, $randoms);
        }
    }

    /**
     * @param  workspace  $workspace
     * @param  array  $contents
     * @param  array  $randoms
     *
     * @throws coding_exception
     * @throws discussion_exception
     */
    private function postDiscussion(workspace $workspace, array $contents, array $randoms = [])
    {
        if (!empty($randoms)) {
            foreach ($randoms as $random) {
                bridge::post_discussion($workspace, $contents[$random]['id']);
            }

            return;
        }

        foreach ($contents as $content) {
            bridge::post_discussion($workspace, $content['id']);
        }
    }
}
