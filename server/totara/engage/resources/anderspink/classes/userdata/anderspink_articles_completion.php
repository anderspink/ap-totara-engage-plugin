<?php

namespace engage_anderspink\userdata;

use coding_exception;
use context;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\entity\engage_resource_completion;
use totara_engage\repository\resource_completion;
use totara_userdata\userdata\export;
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;

final class anderspink_articles_completion extends item
{
    /**
     * @param int $userstatus
     * @return bool
     */
    public static function is_purgeable(int $userstatus)
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function is_exportable()
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function is_countable()
    {
        return true;
    }

    /**
     * @param target_user $user
     * @param context $context
     * @return int|void
     * @throws coding_exception
     */
    protected static function purge(target_user $user, context $context): int
    {
        /** @var resource_completion $repository */
        $repository = engage_resource_completion::repository();
        $repository->delete_by_userid((int) $user->id, anderspink_articles::get_resource_type());

        return self::RESULT_STATUS_SUCCESS;
    }

    /**
     * @param target_user $user
     * @param context $context
     *
     * @return export
     * @throws coding_exception
     */
    protected static function export(target_user $user, context $context): export
    {
        /** @var resource_completion $repository */
        $repository = engage_resource_completion::repository();

        $entities = $repository->get_all((int) $user->id, anderspink_articles::get_resource_type());

        $export       = new export();
        $export->data = [];

        /** @var engage_resource_completion $entity */
        foreach ($entities as $entity) {
            $artcile        = anderspink_articles::from_resource_id($entity->resourceid);
            $export->data[] = [
                'name'         => $artcile->get_name(),
                'time_created' => $entity->timecreated,
            ];
        }

        return $export;
    }

    /**
     * @param target_user $user
     * @param context $context
     * @return int
     * @throws coding_exception
     */
    protected static function count(target_user $user, context $context): int
    {
        /** @var resource_completion $repository */
        $repository = engage_resource_completion::repository();
        return $repository->count_for_resources((int) $user->id, anderspink_articles::get_resource_type());
    }

    /**
     * @return array|string[]
     */
    public static function get_fullname_string()
    {
        return ['user_data_item_article_completed', 'engage_anderspink'];
    }
}