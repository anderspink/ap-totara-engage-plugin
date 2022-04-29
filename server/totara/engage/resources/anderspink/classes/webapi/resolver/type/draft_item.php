<?php

namespace engage_anderspink\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use engage_anderspink\totara_engage\resource\anderspink_articles;

/**
 * Resolver for article draft content.
 */
final class draft_item implements type_resolver
{
    /**
     * A hash maps of the article id and the draft file id.
     *
     * @var array
     */
    private static $maps;

    /**
     * @param string $field
     * @param anderspink_articles $source
     * @param array $args
     * @param execution_context $ec
     *
     * @return mixed
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec)
    {
        global $CFG;

        if (!($source instanceof anderspink_articles)) {
            throw new coding_exception("Invalid parameter source");
        }

        if (!isset(static::$maps)) {
            static::$maps = [];
        }

        switch ($field) {
            case 'content':
                $content = $source->get_content();
                require_once("{$CFG->dirroot}/lib/filelib.php");

                $context = $source->get_context();
                $draftid = null;

                $resource_id = $source->get_id();
                $content     = file_prepare_draft_area(
                    $draftid,
                    $context->id,
                    anderspink_articles::get_resource_type(),
                    anderspink_articles::CONTENT_AREA,
                    $resource_id,
                    null,
                    $content
                );

                static::$maps[$resource_id] = $draftid;

                return $content;

            case 'resourceid':
                return $source->get_id();

            case 'format':
                return $source->get_format();

            case 'file_item_id':
                $resource_id = $source->get_id();
                if (isset(static::$maps[$resource_id])) {
                    return static::$maps[$resource_id];
                }

                require_once("{$CFG->dirroot}/lib/filelib.php");

                $context  = $source->get_content();
                $draft_id = null;

                file_prepare_draft_area(
                    $draft_id,
                    $context->id,
                    anderspink_articles::get_resource_type(),
                    anderspink_articles::CONTENT_AREA,
                    $resource_id
                );

                static::$maps[$resource_id] = $draft_id;
                return $draft_id;

            default:
                debugging("Invalid field '{$field}' that is not existing", DEBUG_DEVELOPER);
                return null;
        }
    }
}