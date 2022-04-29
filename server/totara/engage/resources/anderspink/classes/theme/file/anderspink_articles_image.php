<?php

namespace engage_anderspink\theme\file;

use context;
use context_system;
use core\files\type\file_type;
use core\files\type\web_image;
use core\theme\file\theme_file;
use theme_config;
use totara_core\advanced_feature;

class anderspink_articles_image extends theme_file
{

    /**
     * article_image constructor.
     *
     * @param theme_config|null $theme_config
     * @param string|null $theme
     */
    public function __construct(?theme_config $theme_config = null, ?string $theme = null)
    {
        parent::__construct($theme_config, $theme);
        $this->type = new web_image();
    }

    /**
     * @inheritDoc
     */
    public static function get_id(): string
    {
        return 'engage_anderspink/default';
    }

    /**
     * @return bool
     */
    public function is_enabled(): bool
    {
        return advanced_feature::is_enabled('engage_resources');
    }

    /**
     * @inheritDoc
     */
    public function get_component(): string
    {
        return 'totara_core';
    }

    /**
     * @inheritDoc
     */
    public function get_area(): string
    {
        return 'defaultarticleimage';
    }

    /**
     * @inheritDoc
     */
    public function get_ui_key(): string
    {
        return 'engageresource';
    }

    /**
     * @inheritDoc
     */
    public function get_ui_category(): string
    {
        return 'images';
    }

    /**
     * @inheritDoc
     */
    public function get_type(): file_type
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    protected function get_default_context(?int $tenant_id = null, ?bool $determine_tenant_branding = true): ?context
    {
        // This item is only configurable on the system level at the moment
        return context_system::instance();
    }

}
