<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\dataformatter;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use context;
use context_user;
use engage_anderspink\entity\anderspink_articles;
use engage_anderspink\local\image_processor;
use engage_anderspink\theme\file\anderspink_articles_image;
use moodle_url;
use stdClass;
use totara_catalog\dataformatter\formatter;
use totara_engage\entity\engage_resource;

class image extends formatter
{

    /**
     * @param  string  $ridfield  the database field containing the resource id associated with the article
     * @param  string  $altfield  the database field containing the image alt text
     * @param  string  $owner  the database field containing the user id that created the article
     *
     * @throws coding_exception
     */
    public function __construct(string $ridfield, string $altfield, string $owner)
    {
        $this->add_required_field('resourceid', $ridfield);
        $this->add_required_field('alt', $altfield);
        $this->add_required_field('owner', $owner);
    }

    public function get_suitable_types(): array
    {
        return [
            formatter::TYPE_PLACEHOLDER_IMAGE,
        ];
    }

    /**
     * Given a article id, gets the image.
     *
     * @param  array  $data
     * @param  context  $context
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function get_formatted_value(array $data, context $context): stdClass
    {
        global $PAGE;

        if (!array_key_exists('resourceid', $data)) {
            throw new coding_exception("article image data formatter expects 'resourceid'");
        }

        if (!array_key_exists('owner', $data)) {
            throw new coding_exception("article image data formatter expects 'owner'");
        }

        if (!array_key_exists('alt', $data)) {
            throw new coding_exception("article image data formatter expects 'alt'");
        }

        $image     = new stdClass();
        $context   = context_user::instance($data['owner']);
        $processor = image_processor::make($data['resourceid'], $context->id);

        $resorceObject      = engage_resource::repository()->find($data['resourceid']);
        $anderspinkResoruce = anderspink_articles::repository()->find($resorceObject->instanceid);

        if (!empty($anderspinkResoruce->image)) {
            $image->url = $anderspinkResoruce->image;
        } else {
            $article_image = new anderspink_articles_image();
            $image->url    = $article_image->get_current_or_default_url()->out();
        }

        $image->alt = format_string($data['alt'], true, ['context' => $context]);

        return $image;
    }
}
