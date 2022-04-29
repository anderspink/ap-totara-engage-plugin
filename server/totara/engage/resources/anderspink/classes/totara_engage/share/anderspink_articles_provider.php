<?php
namespace engage_anderspink\totara_engage\share;

use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\share\provider;
use totara_engage\share\shareable;

final class anderspink_articles_provider extends provider {

    /**
     * @inheritDoc
     */
    public function get_item_instance(int $id): shareable {
        return anderspink_articles::from_resource_id($id);
    }

    /**
     * @inheritDoc
     */
    public function update_access(shareable $instance, int $access, int $userid): void {
        $data = ['access' => $access];

        /** @var article $instance */
        $instance->update($data, $userid);
    }

    /**
     * @return string
     */
    public function get_provider_type(): string {
        return 'resource';
    }
}