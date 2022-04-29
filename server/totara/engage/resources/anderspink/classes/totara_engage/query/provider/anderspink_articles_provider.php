<?php

namespace engage_anderspink\totara_engage\query\provider;

use core\orm\query\builder;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use engage_anderspink\entity\anderspink_articles as anderspink_articles_model;
use totara_engage\query\provider\resource_provider;

final class anderspink_articles_provider extends resource_provider
{

    /**
     * @inheritDoc
     */
    public function get_base_builder(): builder
    {
        $builder = parent::get_base_builder();
        $builder->join([anderspink_articles_model::TABLE, 'ap'], 'ap.id', '=', 'er.instanceid');
        $builder = $builder->where('er.resourcetype', '=', anderspink_articles::get_resource_type());
        return $builder;
    }

    /**
     * @inheritDoc
     */
    protected function get_resource_type(): string
    {
        return anderspink_articles::get_resource_type();
    }

}