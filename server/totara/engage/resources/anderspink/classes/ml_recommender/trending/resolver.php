<?php
namespace engage_anderspink\ml_recommender\trending;

use engage_anderspink\totara_engage\resource\anderspink_articles;
use ml_recommender\trending\trending;
use totara_engage\access\accessible;

final class resolver implements trending {

    /**
     * @inheritDoc
     */
    public function get_item_instance(int $id): accessible {
        return anderspink_articles::from_resource_id($id);
    }

}