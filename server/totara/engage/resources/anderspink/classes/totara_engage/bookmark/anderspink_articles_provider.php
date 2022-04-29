<?php

namespace engage_anderspink\totara_engage\bookmark;

use coding_exception;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\access\accessible;
use totara_engage\bookmark\provider;

final class anderspink_articles_provider extends provider
{

    /**
     * @inheritDoc
     * @throws coding_exception
     */
    public function get_item_instance(int $id): accessible
    {
        return anderspink_articles::from_resource_id($id);
    }
}