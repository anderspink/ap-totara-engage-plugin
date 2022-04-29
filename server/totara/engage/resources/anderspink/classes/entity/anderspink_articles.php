<?php

namespace engage_anderspink\entity;

use core\orm\entity\entity;

/**
 * Class anderspink_articles
 *
 * @package container_anderspink\entity
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $date_published
 * @property string $url
 * @property string $author
 * @property string $domain
 * @property string $content
 * @property int $format
 * @property int $timeview
 * @property int $team
 * @property int $api_id
 * @property bool $sync
 * @property bool $bridge
 * @property string $api_content
 */
class anderspink_articles extends entity
{
    public const TABLE = 'engage_anderspink_articles';
}