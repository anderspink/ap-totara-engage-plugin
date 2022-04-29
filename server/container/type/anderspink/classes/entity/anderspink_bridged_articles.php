<?php

namespace container_anderspink\entity;

use core\orm\entity\entity;

/**
 * Class anderspink_bridged_articles
 *
 * @package container_anderspink\entity
 * @property int $id
 * @property int $bridge
 * @property int $article
 */
final class anderspink_bridged_articles extends entity
{
    public const TABLE = 'anderspink_bridged_articles';
}