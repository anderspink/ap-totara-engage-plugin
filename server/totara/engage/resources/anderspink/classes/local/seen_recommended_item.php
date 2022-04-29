<?php

namespace engage_anderspink\local;

use coding_exception;
use core\orm\query\builder;
use ml_recommender\entity\recommended_user_item;
use totara_core\advanced_feature;

final class seen_recommended_item
{
    /**
     * @param int $user_id
     * @param int $item_id
     * @param string $component
     * @throws coding_exception
     */
    public static function process_seen_event(int $user_id, int $item_id, string $component): void
    {
        if (advanced_feature::is_disabled('ml_recommender')) {
            return;
        }

        $builder = builder::table(recommended_user_item::TABLE);
        $builder->where('user_id', $user_id);
        $builder->where('item_id', $item_id);
        $builder->where('component', $component);
        $builder->where('seen', 0);

        $attrs       = new \stdClass();
        $attrs->seen = (int) 1;

        $builder->update($attrs);
    }
}