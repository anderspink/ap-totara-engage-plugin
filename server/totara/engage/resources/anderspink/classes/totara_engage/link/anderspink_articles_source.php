<?php
namespace engage_anderspink\totara_engage\link;

use totara_engage\link\source_generator;

final class anderspink_articles_source extends source_generator {
    public static function get_source_key(): string {
        return 'ap';
    }

    /**
     * @param array $source_params
     * @return array
     */
    public static function convert_source_to_attributes(array $source_params): array {
        return [
            'id' => current($source_params),
        ];
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function convert_attributes_to_source(array $attributes): array {
        return [
            $attributes['id'],
        ];
    }
}