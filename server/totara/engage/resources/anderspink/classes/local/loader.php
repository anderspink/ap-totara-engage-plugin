<?php
namespace engage_anderspink\local;

use coding_exception;
use core\orm\paginator;
use core\orm\query\builder;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\entity\engage_resource;
use engage_article\entity\article as entity;

final class loader {
    private function __construct() {}

    /**
     * @param int $userid
     * @param int $page Setting $page to zero means that it will query all the record.
     *
     * @return paginator
     * @throws coding_exception
     */
    public static function load_all_article_of_user(int $userid, int $page = 1): paginator {
        $builder = builder::table(engage_resource::TABLE, 'er');
        $builder->join([entity::TABLE, 'ap'], 'er.instanceid', 'ap.id');

        $builder->where('er.resourcetype', anderspink_articles::get_resource_type());
        $builder->where('er.userid', $userid);
        $builder->results_as_arrays();

        $builder->select(
            [
                // Resource fields.
                'er.id',
                'er.instanceid',
                'er.name',
                'er.resourcetype',
                'er.userid',
                'er.access',
                'er.timecreated',
                'er.timemodified',
                'er.extra',
                'er.contextid',

                // Article fields.
                'ap.content',
                'ap.format'
            ]
        );

        $builder->map_to(
            function (array $record) {
                $resource = engage_resource::from_record($record);

                $entity = new entity();
                $entity->id = $resource->instanceid;
                $entity->content = $record['content'];
                $entity->format = $record['format'];

                return anderspink_articles::from_entity($entity, $resource);
            }
        );

        return $builder->paginate($page);
    }
}