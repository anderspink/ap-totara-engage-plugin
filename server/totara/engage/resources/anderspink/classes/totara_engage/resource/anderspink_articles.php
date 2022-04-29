<?php

namespace engage_anderspink\totara_engage\resource;

use coding_exception;
use container_anderspink\entity\anderspink_bridged_articles;
use context_user;
use core\orm\query\builder;
use dml_exception;
use engage_anderspink\entity\anderspink_articles as anderspink_articles_entity;
use engage_anderspink\event\anderspink_articles_created;
use engage_anderspink\event\anderspink_articles_deleted;
use engage_anderspink\event\anderspink_articles_reshared;
use engage_anderspink\event\anderspink_articles_shared;
use engage_anderspink\event\anderspink_articles_updated;
use engage_anderspink\local\helper;
use engage_anderspink\local\image_processor;
use engage_anderspink\totara_engage\resource\input\name_validator;
use moodle_exception;
use moodle_url;
use stdClass;
use Throwable;
use totara_comment\comment_helper;
use totara_engage\access\access;
use totara_engage\access\access_manager;
use totara_engage\entity\engage_resource;
use totara_engage\link\builder as link_builder;
use totara_engage\resource\input\access_validator;
use totara_engage\resource\input\definition;
use totara_engage\resource\input\topic_validator;
use totara_engage\resource\resource_item;
use totara_engage\share\manager as share_manager;
use totara_engage\share\share as share_model;
use totara_engage\timeview\time_viewable;
use totara_reaction\reaction_helper;

/**
 * Model for article resource. All the files within article's content will be stored within
 * the resource's id.
 */
final class anderspink_articles extends resource_item implements time_viewable
{
    /**
     * This constant is for the any area that is related to the content of the article itself.
     *
     * @var string
     */
    public const CONTENT_AREA = 'content';

    /**
     * @var string
     */
    public const IMAGE_AREA = 'image';

    /**
     * @var string
     */
    public const REACTION_AREA = 'media';

    /**
     * Using for comment area.
     *
     * @var string
     */
    public const COMMENT_AREA = 'comment';

    /**
     * @var anderspink_articles_entity
     */
    private $article;

    /**
     * @return int
     */
    public function get_articleid(): int
    {
        return $this->article->id;
    }

    /**
     * @return string
     */
    public function get_content(): string
    {
        return $this->article->content;
    }

    /**
     * @return int
     */
    public function get_format(): int
    {
        return $this->article->format;
    }

    /**
     * Constructing a model of article.
     *
     * @param anderspink_articles_entity $article
     * @param engage_resource $entity
     *
     * @return anderspink_articles
     * @throws coding_exception
     */
    public static function from_entity(
        anderspink_articles_entity $article,
        engage_resource $entity
    ): anderspink_articles {
        $resourcetype = static::get_resource_type();
        if ($resourcetype != $entity->resourcetype) {
            throw new coding_exception("Invalid resource record that is used for different component");
        }

        if (!$entity->exists() || !$article->exists()) {
            throw new coding_exception("Either resource record or the article record is not being populated");
        } else {
            if ($entity->instanceid != $article->id) {
                throw new coding_exception("Resource record is not meant for the article");
            }
        }

        $resource          = new anderspink_articles($entity);
        $resource->article = $article;

        return $resource;
    }

    /**
     * @param int $resourceid
     * @return anderspink_articles
     * @throws coding_exception
     */
    public static function from_resource_id(int $resourceid): resource_item
    {
        /** @var anderspink_articles $resource */
        $resource = parent::from_resource_id($resourceid);

        if ($resource->get_resourcetype() !== static::get_resource_type()) {
            throw new coding_exception('Resource type is not meant for the article');
        }

        $instanceid        = $resource->get_instanceid();
        $resource->article = new anderspink_articles_entity($instanceid);

        return $resource;
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function can_delete(int $userid): bool
    {
        $owner = $this->get_userid();
        if ($owner == $userid) {
            // Same user origin.
            return true;
        }

        return access_manager::can_manage_engage($this->get_context(), $userid);
    }

    /**
     * @param int $userid
     * @return bool
     * @throws coding_exception
     */
    public static function can_create(int $userid): bool
    {
        $context = context_user::instance($userid);
        return has_capability('engage/article:create', $context, $userid);
    }

    /**
     * @param array $data
     * @param engage_resource $entity
     * @param int $userid
     *
     * @return int
     */
    public static function do_create(array $data, engage_resource $entity, int $userid): int
    {
        $article = new anderspink_articles_entity();

        $article->name           = $data['name'];
        $article->image          = $data['image'];
        $article->date_published = $data['date_published'];
        $article->url            = $data['url'];
        $article->author         = $data['author'];
        $article->domain         = $data['domain'];
        $article->content        = $data['content'];
        $article->format         = $data['format'];
        $article->team           = $data['team'];
        $article->api_id         = $data['api_id'];
        $article->api_content    = $data['api_content'] ?? '';

        if (isset($data['timeview'])) {
            $article->timeview = $data['timeview'];
        }

        if ($data['sync'] == true) {
            $article->sync = true;
        }

        $article->save();

        return $article->id;
    }

    /**
     * @param  resource_item  $item
     * @param  array  $data
     * @param  int|null  $user_id
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function post_create(resource_item $item, array $data, ?int $user_id = null): void
    {
        global $DB;

        $event = anderspink_articles_created::from_article($item, $user_id);
        $event->trigger();

        $resourceExtra           = json_decode($item->get_extra());
        $resourceExtra->image    = $data['image'];
        $resourceExtra->alt_text = str_replace(' ', '_', $data['name']);
        $resourceExtra->timeview = $data['timeview'];

        $data        = $DB->get_record(engage_resource::TABLE, ['id' => $item->get_id()]);
        $data->extra = json_encode($resourceExtra);

        $DB->update_record(engage_resource::TABLE, $data);
    }

    /**
     * @param int $userid
     * @return bool
     * @throws Throwable
     */
    protected function do_delete(int $userid): bool
    {
        global $DB;

        $event = builder::get_db()->transaction(function () use ($userid, $DB) {
            share_manager::delete($this->get_id(), static::get_resource_type());

            // Deleting comments.
            comment_helper::purge_area_comments(
                static::get_resource_type(),
                'comment',
                $this->resource->id
            );

            // Deleting reaction from the article.
            reaction_helper::purge_area_reactions(
                static::get_resource_type(),
                'media',
                $this->resource->id
            );

            helper::delete_files($this);

            // Delete the attached image file.
            $resourceid = $this->get_id();
            $contextid  = $this->get_context_id();
            $processor  = image_processor::make($resourceid, $contextid);
            $processor->delete_existing_image();

            $event = anderspink_articles_deleted::from_articles($this, $userid);

            $this->article->delete();
            $this->resource->delete();

            $DB->delete_records(anderspink_bridged_articles::TABLE, ['article' => $this->article->id]);

            return $event;
        });

        $event->trigger();

        return true;
    }

    /**
     * @return array
     */
    protected static function get_data_definitions(): array
    {
        return [
            definition::from_parameters(
                'access',
                [
                    'default'    => access::PRIVATE,
                    'validators' => [new access_validator()],
                ]
            ),
            definition::from_parameters(
                'name',
                [
                    'required-on-add' => true,
                    'validators'      => [
                        new name_validator(),
                    ],
                ]
            ),
            definition::from_parameters(
                'topics',
                [
                    'default'    => [],
                    'validators' => [new topic_validator()],
                ]
            ),
            definition::from_parameters('content', ['required-on-add' => true]),
            definition::from_parameters('format', ['default' => FORMAT_HTML]),
            definition::from_parameters('timeview', ['default' => null]),
        ];
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function can_update(int $userid): bool
    {
        $owner = $this->get_userid();

        if ($owner == $userid) {
            return true;
        }

        return access_manager::can_manage_engage($this->get_context(), $userid);
    }

    /**
     * @param array $data
     * @param int $userid
     *
     * @return bool
     */
    protected function do_update(array $data, int $userid): bool
    {
        global $CFG;

        if (isset($data['content'])) {
            // Only updating the content and the fortmat of its content when it is specified in the update data.
            // Otherwise, we will skip it, but should not skip the event, as it can be updated with name
            // or something  else.

            $format = $this->article->format;

            if (isset($data['format'])) {
                // Only update the format if it specified, otherwise we re-use the current format.
                $format = $data['format'];
            }

            $this->article->content = $data['content'];
            if (isset($data['draft_id'])) {
                // Simulate the form data for editor to handle file
                require_once("{$CFG->dirroot}/lib/filelib.php");
                $formdata                 = new stdClass();
                $formdata->content_editor = [
                    'text'   => $data['content'],
                    'format' => $format,
                    'itemid' => $data['draft_id'],
                ];

                $context = context_user::instance($this->get_userid());
                $options = helper::get_editor_options($context);

                $formdata = file_postupdate_standard_editor(
                    $formdata,
                    'content',
                    $options,
                    $context,
                    static::get_resource_type(),
                    static::CONTENT_AREA,
                    $this->resource->id
                );

                $this->article->content = $formdata->content;
            }

            $this->article->format = $format;

            // Download & save the image attached to the article, only if content was changed
            $resourceid = $this->get_id();
            $contextid  = $this->get_context()->id;
            $processor  = image_processor::make($resourceid, $contextid);
            $processor->extract_image_from_content($this->article->content, $format);
        }

        if (isset($data['timeview'])) {
            $this->article->timeview = $data['timeview'];
        }

        $this->article->update();
        return true;
    }

    /**
     * Triggering event for update.
     *
     * @param int|null $user_id The actor's id who is responsible for the whole process.
     * @return void
     */
    protected function post_update(?int $user_id = null): void
    {
        global $DB;

        $event = anderspink_articles_updated::from_article($this, $user_id);
        $event->trigger();

        $resourceExtra           = json_decode($this->get_extra());
        $resourceExtra->image    = $this->article->image;
        $resourceExtra->alt_text = str_replace(' ', '_', $this->get_name());
        $resourceExtra->timeview = $this->get_timeview();

        $data        = $DB->get_record(engage_resource::TABLE, ['id' => $this->get_id()]);
        $data->extra = json_encode($resourceExtra);

        $DB->update_record(engage_resource::TABLE, $data);
    }

    /**
     * @return array
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function to_array(): array
    {
        $data             = parent::to_array();
        $data['content']  = $this->article->content;
        $data['format']   = $this->article->format;
        $data['timeview'] = isset($this->article->timeview) ? $this->article->timeview : null;
        $image            = $this->get_image();
        $data['image']    = $image ? $image->out() : null;

        return $data;
    }

    /**
     * @return int|null
     */
    public function get_timeview(): ?int
    {
        return isset($this->article->timeview) ? $this->article->timeview : null;
    }

    /**
     * @return moodle_url|null
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_image(): ?\moodle_url
    {
        $extra = $this->resource->get_json_decoded();
        $url   = null;
        if (!empty($extra['image'])) {
            $url = new moodle_url($extra['image']);
        }

        return $url;
    }

    /**
     * @inheritDoc
     */
    public function can_share(int $userid): bool
    {
        // Check if user is allowed to share articles.
        $context = $this->get_context();
        if (!has_capability('engage/article:share', $context, $userid)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function shared(share_model $share): void
    {
        // Create a shared event.
        if (!$share->is_notified()) {
            $event = anderspink_articles_shared::from_share($share);
            $event->trigger();
        }
    }

    /**
     * @param int $userid
     * @throws coding_exception
     */
    public function reshare(int $userid): void
    {
        $event = anderspink_articles_reshared::from_articles($this, $userid);
        $event->trigger();
    }

    /**
     * @inheritDoc
     *
     * @param bool $reload
     * @return void
     * @throws coding_exception
     */
    public function refresh(bool $reload = false): void
    {
        if ($reload) {

            $extra = [
                'timeview' => $this->article->timeview,
                'image'    => null,
                'alt_text' => '',
            ];

            $resourceid = $this->get_id();
            $contextid  = $this->get_context()->id;
            $processor  = image_processor::make($resourceid, $contextid);
            $image      = $processor->get_image();
            if (null !== $image) {
                $moodle_url = \moodle_url::make_pluginfile_url(
                    $image->get_contextid(),
                    $image->get_component(),
                    $image->get_filearea(),
                    $image->get_itemid(),
                    $image->get_filepath(),
                    $image->get_filename()
                );

                $extra['image'] = $moodle_url->out_as_local_url(false);

                // Save customized alt text to card extra.
                $extra['alt_text'] = $processor->get_image_alt_text($this->get_content());
            }

            $this->resource->extra = $extra;
            $this->resource->update_timestamps(false);
            $this->resource->save();
        }

        $this->resource->refresh();
        $this->article->refresh();
    }

    /**
     * @param int $instanceid
     * @return int
     */
    public static function get_resource_usage(int $instanceid): int
    {
        return parent::get_resource_usage($instanceid);
    }

    /**
     * @return string
     */
    public function get_url(): string
    {
        return link_builder::to(self::get_resource_type(), ['id' => $this->get_id()])->out(true);
    }

    /**
     * @inheritDoc
     */
    public function can_unshare(int $sharer_id, ?bool $is_container = false): bool
    {
        global $USER;

        // Sharer can not be owner of resources If resource is shared to share_with_me,
        // but sharer can be the owner if resource is shared to the container.
        if (!$is_container) {
            if ($sharer_id == $this->get_userid()) {
                return false;
            }
        }

        return has_capability_in_any_context('engage/anderspink:manage', null, $USER->id);
    }

    /**
     * @param string $atribute
     * @return mixed|string|null
     */
    public function get_attribute(string $atribute): ?string
    {
        return $this->article->{$atribute};
    }
}