<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package engage_article
 */

namespace engage_anderspink\watcher;

use core\orm\query\builder;
use engage_anderspink\entity\anderspink_articles as entity;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use moodle_exception;
use stdClass;
use totara_topic\hook\get_deleted_topic_usages;

/**
 * Watcher for deleted topic usage.
 */
final class totara_topic_watcher
{
    /**
     * For injecting the information of the affected article.
     *
     * @param get_deleted_topic_usages $hook
     * @return void
     * @throws moodle_exception
     */
    public static function on_deleted_topic_get_usage(get_deleted_topic_usages $hook): void
    {
        $item_type    = $hook->get_item_type();
        $instance_ids = $hook->get_instance_ids();

        if ('engage_resource' !== $item_type || empty($instance_ids)) {
            return;
        }

        $builder = builder::table(entity::TABLE, 'ap');
        $builder->join(
            ['engage_resource', 'er'],
            function (builder $join): void {
                $join->where_field('er.instanceid', 'ap.id');
                $join->where('er.resourcetype', anderspink_articles::get_resource_type());
            }
        );

        $builder->where_in('er.id', $instance_ids);
        $builder->select(['er.id', 'er.name', 'er.userid']);

        $records = $builder->fetch();

        /** @var stdClass $record */
        foreach ($records as $record) {
            $hook->add_item(
                $record->userid,
                format_string($record->name),
                new \moodle_url("/totara/engage/resources/anderspink_article/index.php", ['id' => $record->id])
            );
        }
    }
}