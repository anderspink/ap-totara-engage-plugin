<?php

namespace engage_anderspink\userdata;

use coding_exception;
use context;
use dml_exception;
use engage_anderspink\local\helper;
use engage_anderspink\local\loader;
use engage_anderspink\totara_engage\resource\anderspink_articles as anderspink_articles_resource;
use totara_userdata\userdata\export;
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;

/**
 * Handles purging, counting and exporting the article resource types created by the user.
 */
final class anderspink_articles extends item
{

    /**
     * String used for human readable name of this item.
     *
     * @return array parameters of get_string($identifier, $component) to get full item name and optionally help.
     */
    public static function get_fullname_string()
    {
        return ['user_data_item_article', 'engage_anderspink'];
    }

    /**
     * Can user data of this item data be purged from system at this time?
     *
     * @param int $userstatus target_user::STATUS_ACTIVE, target_user::STATUS_DELETED or target_user::STATUS_SUSPENDED
     * @return bool
     */
    public static function is_purgeable(int $userstatus): bool
    {
        return true;
    }

    /**
     * Purge user data for this item.
     *
     * NOTE: Remember that context record does not exist for deleted users any more,
     *       it is also possible that we do not know the original user context id.
     *
     * @param target_user $user
     * @param context $context restriction for purging e.g., system context for everything, course context for purging one course
     * @return int result self::RESULT_STATUS_SUCCESS, self::RESULT_STATUS_ERROR or status::RESULT_STATUS_SKIPPED
     * @throws coding_exception
     * @throws dml_exception
     */
    protected static function purge(target_user $user, context $context)
    {
        $paginator = loader::load_all_article_of_user((int) $user->id, 0);
        $articles  = $paginator->get_items()->all();

        foreach ($articles as $article) {
            helper::purge_article($article);
        }

        return self::RESULT_STATUS_SUCCESS;
    }

    /**
     * Can user data of this item data be exported from system?
     *
     * @return bool
     */
    public static function is_exportable(): bool
    {
        return true;
    }

    /**
     * Export user data from this item.
     *
     * @param target_user $user
     * @param context $context restriction for exporting i.e., system context for everything and course context for course export
     * @return export|int result object or integer error code self::RESULT_STATUS_ERROR or self::RESULT_STATUS_SKIPPED
     * @throws coding_exception
     */
    protected static function export(target_user $user, context $context)
    {
        $paginator = loader::load_all_article_of_user((int) $user->id, 0);
        $resources = $paginator->get_items()->all();

        $export       = new export();
        $export->data = [];

        /** @var anderspink_articles_resource $resource */
        foreach ($resources as $resource) {
            $export->data[] = [
                'name'         => $resource->get_name(),
                'content'      => content_to_text($resource->get_content(), $resource->get_format()),
                'timecreated'  => $resource->get_timecreated(),
                'timemodified' => $resource->get_timemodified(),
            ];
        }

        return $export;
    }

    /**
     * Can user data of this item be somehow counted?
     *
     * @return bool
     */
    public static function is_countable(): bool
    {
        return true;
    }

    /**
     * Count user data for this item.
     *
     * @param target_user $user
     * @param context $context restriction for counting i.e., system context for everything and course context for course data
     * @return int amount of data or negative integer status code (self::RESULT_STATUS_ERROR or self::RESULT_STATUS_SKIPPED)
     * @throws coding_exception
     */
    protected static function count(target_user $user, context $context): int
    {
        $paginator = loader::load_all_article_of_user((int) $user->id);
        return $paginator->get_total();
    }
}