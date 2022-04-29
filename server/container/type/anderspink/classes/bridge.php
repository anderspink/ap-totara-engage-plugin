<?php

namespace container_anderspink;

use coding_exception;
use container_anderspink\entity\anderspink_api;
use container_anderspink\entity\anderspink_bridge;
use container_anderspink\entity\anderspink_bridged_articles;
use container_anderspink\entity\anderspink_discussion_index;
use container_anderspink\event\anderspink_bridge_created;
use container_anderspink\event\anderspink_bridge_delete_old_content;
use container_anderspink\local\anderspink_helper;
use container_anderspink\local\AnderspinkApiClient;
use container_anderspink\share\manager;
use container_anderspink\task\delete_anderspink_old_content_adhoc_task;
use container_workspace\discussion\discussion_helper;
use container_workspace\event\workspace_deleted;
use container_workspace\exception\discussion_exception;
use container_workspace\query\workspace\access;
use container_workspace\totara_engage\share\recipient\library;
use container_workspace\workspace;
use context_user;
use core\orm\query\builder;
use core\orm\query\exceptions\multiple_records_found_exception;
use core\orm\query\exceptions\record_not_found_exception;
use core_tag\entity\tag;
use core_tag\entity\tag_collection;
use dml_exception;
use dml_transaction_exception;
use engage_anderspink\entity\anderspink_articles as entity;
use engage_anderspink\local\helper as engage_anderspink_helper;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\card\card_loader;
use totara_engage\entity\engage_resource;
use totara_engage\entity\share;
use totara_engage\entity\share_recipient;
use totara_engage\query\query;
use totara_engage\resource\resource_item;
use totara_engage\share\recipient\manager as recipient_manager;

final class bridge
{

    private const MAX_SYNC_ARTICLES = 50;
    private const MAX_FETCH_LIMIT   = 5;

    /**
     * @return array
     * @throws coding_exception
     */
    public static function all(): array
    {
        $records     = anderspink_bridge::repository()->get();
        $returnArray = [];

        foreach ($records as $record) {
            $team      = anderspink::from_id($record->team);
            $workspace = workspace::from_id($record->workspace);
            $typeClass = anderspink_helper::RESOURCETYPE[$record->type];

            /** @var board|briefing|bool $type */
            $type = $typeClass::from_apiid($record->type_id);

            if (!$type) {
                continue;
            }

            $returnArray[] = (object)[
                'workspaceid'    => $workspace->get_id(),
                'workspace_name' => $workspace->get_name(),
                'teamid'         => $team->get_id(),
                'team_name'      => $team->team_name,
                'type_name'      => $type->name,
                'bridge_type'    => $record->type,
                'id'             => $record->id,
            ];
        }

        return $returnArray;
    }

    /**
     * @param  int  $id
     *
     * @return bool
     * @throws coding_exception
     */
    public static function delete(int $id): bool
    {
        $bridge = anderspink_bridge::repository()->find($id);

        if (!empty($bridge) && $bridge->exists()) {
            $bridge->delete();
        }

        $articles = anderspink_bridged_articles::repository()->find($id);

        if (!empty($articles) && $articles->exists()) {
            $articles->delete();
        }

        return true;
    }

    /**
     * @param  anderspink_bridge_created  $event
     *
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function bridge_created(anderspink_bridge_created $event)
    {
        global $DB;

        $articles = [];

        $anderspinkApi = anderspink_api::repository()->find($event->other['teamid']);

        if (empty($anderspinkApi) || !$anderspinkApi->exists()) {
            return;
        }

        $team   = anderspink::from_id($event->other['teamid']);
        $bridge = anderspink_bridge::repository()->find($event->objectid);

        $apiClient   = new AnderspinkApiClient($team->api_key);
        $apiArticles = $apiClient->fetchArticles(
            $event->other['apiid'],
            $event->other['endpoint'],
            1,
            self::MAX_FETCH_LIMIT,
            $event->other['sync']
        );

        foreach ($apiArticles->articles as $article) {
            $articles[] = $article;
        }

        $articles = array_splice($articles, 0, self::MAX_SYNC_ARTICLES);

        $topicId = self::createTag($event->other['resourcename']);
        $shares  = [
            [
                'instanceid' => $event->other['workspaceid'],
                'component'  => 'container_workspace',
                'area'       => 'library',
            ],
        ];

        //Add missing ones
        foreach ($articles as $article) {
            $record = entity::repository()
                            ->where('api_id', $article->id)
                            ->where('sync', true)
                            ->where('team', $team->get_id());

            if (empty($record) || !$record->exists()) {
                $data = (object)[
                    'resourcetype'   => anderspink_articles::get_resource_type(),
                    'name'           => $article->title,
                    'format'         => FORMAT_HTML,
                    'topics'         => [$topicId],
                    'shares'         => $shares,
                    'access'         => access::PUBLIC,
                    'image'          => $article->image,
                    'url'            => $article->url,
                    'domain'         => $article->domain,
                    'date_published' => $article->date_published,
                    'author'         => $article->author,
                    'timeview'       => engage_anderspink_helper::generate_read_time($article->reading_time / 60),
                    'content'        => engage_anderspink_helper::generate_content_html(
                        (object)[
                            'name'      => $article->title,
                            'summary'   => $article->content ?? '',
                            'url'       => $article->url,
                            'domain'    => $article->domain,
                            'published' => $article->date_published,
                            'briefing'  => $event->other['resourcename'],
                        ],
                        (object)[
                            'url'  => $article->image,
                            'alt'  => $article->title,
                            'attr' => ['width' => '100%'],
                        ]
                    ),
                    'team'           => $team->get_id(),
                    'api_id'         => $article->id,
                    'sync'           => true,
                    'api_content'    => json_encode($article),
                    'user'           => $bridge->user,
                ];

                $record = self::createEngageResource($data);
            } else {
                $record = $record->one(false);
                $record = anderspink_articles::from_instance($record->id, anderspink_articles::get_resource_type());
            }

            $share = self::share_exsists($event->other['workspaceid'], $bridge->id, $record->get_instanceid());

            if (!$share) {
                $trans      = $DB->start_delegated_transaction();
                $recipients = recipient_manager::create_from_array($shares);

                manager::share_manually($record, $record::get_resource_type(), $recipients, $bridge->user);

                $anderspinkBridgeArticle = anderspink_bridged_articles::repository()
                                                                      ->where('article', $record->get_instanceid())
                                                                      ->where('bridge', $bridge->id);

                if (empty($anderspinkBridgeArticle) | !$anderspinkBridgeArticle->exists()) {
                    $bridgedRecord          = new anderspink_bridged_articles();
                    $bridgedRecord->article = $record->get_instanceid();
                    $bridgedRecord->bridge  = $bridge->id;

                    $bridgedRecord->save();
                }

                $DB->commit_delegated_transaction($trans);
                sleep(1);
            }
        }

        $workspace = workspace::from_id($event->other['workspaceid']);

        $data = [
            'objectid'  => $bridge->id,
            'contextid' => $workspace->get_context()->id,
            'other'     => [
                'workspaceid' => $workspace->get_id(),
                'bridgeid'    => $bridge->id,
            ],
        ];

        $adhoc = new delete_anderspink_old_content_adhoc_task();
        $adhoc->set_custom_data($data);

        \core\task\manager::queue_adhoc_task($adhoc);
    }

    /**
     * @param  anderspink_bridge_delete_old_content  $event
     *
     * @throws dml_transaction_exception
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function delete_old_content(anderspink_bridge_delete_old_content $event)
    {
        global $DB;

        $shares        = self::get_anderspink_article_in_workspace($event->other['workspaceid'], $event->other['bridgeid']);
        $notInteracted = [];

        foreach ($shares as $share) {
            $engageShare          = builder::table('engage_share')->where('id', $share->shareid)->one();
            $checkForInteractions = self::check_for_interactions($engageShare->itemid, [
                'reaction' => anderspink_articles::REACTION_AREA,
                'comment'  => anderspink_articles::COMMENT_AREA,
            ]);

            if (!$checkForInteractions) {
                $notInteracted[] = $share;
            }
        }

        $shares            = $notInteracted;
        $noOfSharedRecords = sizeof($shares);

        if ($noOfSharedRecords > self::MAX_SYNC_ARTICLES) {
            usort($shares, function ($a, $b) {
                return $b->timecreated - $a->timecreated;
            });

            $shares = array_slice($shares, self::MAX_SYNC_ARTICLES);

            foreach ($shares as $record) {
                $trans         = $DB->start_delegated_transaction();
                $engageArticle = entity::repository()->find($record->articleid);

                share_recipient::repository()
                               ->where('shareid', $record->shareid)
                               ->where('instanceid', $event->other['workspaceid'])
                               ->where('area', 'library')
                               ->where('component', workspace::get_type())
                               ->delete();

                anderspink_bridged_articles::repository()
                                           ->where('bridge', $event->other['bridgeid'])
                                           ->where('article', $record->articleid)
                                           ->delete();

                $engageArticle = entity::repository()->find($record->articleid);

                if (!empty($engageArticle) && $engageArticle->exists()) {
                    $engageArticle->delete();
                }

                $DB->commit_delegated_transaction($trans);
            }
        }
    }

    /**
     * @param  workspace  $workspace
     * @param  int  $articleId
     * @param  int  $userId
     *
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     * @throws coding_exception
     * @throws discussion_exception
     * @throws dml_exception
     */
    public static function post_discussion(workspace $workspace, int $articleId, int $userId = 0)
    {
        if (empty($userId)) {
            $role = builder::table('role')->where('shortname', 'workspacediscussionauthor')->one();
            $user = get_role_users($role->id, \context_system::instance());

            if (sizeof($user) === 1) {
                $userId = end($user)->id;
            }
        }

        if (empty($userId)) {
            $userId = builder::table('config')->where('name', 'anderspink_discussion_post_user')->one();
            $userId = $userId->value;
        }

        if (empty($userId)) {
            $userId = (get_admin())->id;
        }

        $article = builder::table(engage_resource::TABLE)->where('instanceid', $articleId);

        if (empty($article) || !$article->exists()) {
            builder::table(entity::TABLE)->where('id', $articleId)->delete();

            return;
        }

        /** @var anderspink_articles $article */
        $engageResource = new engage_resource($articleId);
        $article        = anderspink_articles::from_instance($engageResource->instanceid, $engageResource->resourcetype);
        $articleApi     = json_decode($article->get_attribute('api_content'));
        $image          = null;

        if ($articleApi->image) {
            $image = (object)[
                'url'  => $articleApi->image,
                'alt' => str_replace(' ', '_', $article->get_name()),
                'attr' => ['width' => 400],
            ];
        }

        $content = engage_anderspink_helper::generate_content_html(
               (object)[
                'name'      => $article->get_name(),
                'summary'   => $articleApi->content,
                'url'       => $article->get_attribute('url'),
                'domain'    => $articleApi->domain,
                'published' => $article->get_attribute('date_published'),
            ], $image ?? null, 'discussion'
        );

        $discussion = discussion_helper::create_discussion($workspace, $content, null, FORMAT_HTML, $userId);

        $index              = new anderspink_discussion_index();
        $index->discussion = $discussion->get_id();
        $index->timecreated = time();

        $index->save();

        sleep(1); //Sleep here for 1s so we do not run to milliseconds case as we did with articles.
    }

    /**
     * @param  string  $name
     *
     * @return int
     * @throws coding_exception
     */
    private static function createTag(string $name): int
    {
        global $USER;

        $records = tag::repository()->get();
        $tagName = trim(strtolower($name));

        foreach ($records as $record) {
            if ($record->name === $tagName) {
                return $record->id;
            }
        }

        $tagCollection = tag_collection::repository()->select('id')->where('component', 'totara_topic')->one();

        $newTag = new tag();

        $newTag->userid            = $USER->id;
        $newTag->tagcollid = $tagCollection->id;
        $newTag->name      = trim(strtolower($name));
        $newTag->rawname   = $name;
        $newTag->isstandard = 1;
        $newTag->description = '';
        $newTag->descriptionformat = 0;
        $newTag->flag              = 0;
        $newTag->timemodified      = time();

        $newTag->save();

        return $newTag->id;
    }

    /**
     * @param  object  $resource
     *
     * @return anderspink_articles|resource_item
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function createEngageResource(object $resource)
    {
        $context = context_user::instance($resource->user);

        $record               = new engage_resource();
        $record->resourcetype = $resource->resourcetype;
        $record->name         = $resource->name;
        $record->userid       = $resource->user;
        $record->contextid    = $context->id;
        $record->access       = $resource->access;
        $record->instanceid   = anderspink_articles::do_create((array)$resource, $record, $resource->user);

        $record->save();

        /** @var anderspink_articles $anderspinkArticle */
        $anderspinkArticle = anderspink_articles::from_resource_id($record->id);

        $anderspinkArticle->add_topics_by_ids($resource->topics);
        $anderspinkArticle->refresh(true);

        $anderspinkArticle::post_create($anderspinkArticle, (array)$resource, $resource->user);

        return $anderspinkArticle;
    }

    /**
     * @param  int  $workspaceId
     * @param  array|null  $filter
     *
     * @return \core\orm\pagination\offset_cursor_paginator
     * @throws coding_exception
     */
    public static function fetchWorkspaceArticles(int $workspaceId, array $filter = null)
    {
        $query = new query();
        $query->set_component('container_workspace');
        $query->set_area('library');

        if ($filter) {
            $query->set_filters($filter);
        }

        $recipient = new library($workspaceId);
        $loader    = new card_loader($query);

        return $loader->fetch_shared($recipient);
    }

    /**
     * @param  workspace_deleted  $event
     *
     * @throws coding_exception
     */
    public static function workspace_deleted(workspace_deleted $event)
    {
        anderspink_bridge::repository()->where('workspace', $event->objectid)->delete();
    }

    /**
     * @param  int  $workspaceId
     * @param  int  $bridgeId
     *
     * @return array
     * @throws coding_exception
     */
    private static function get_anderspink_article_in_workspace(int $workspaceId, int $bridgeId): array
    {
        $shares         = [];
        $workspaceItems = share_recipient::repository()
                                         ->where('instanceid', $workspaceId)
                                         ->where('component', workspace::get_type())
                                         ->get();

        foreach ($workspaceItems as $workspaceItem) {
            $share = share::repository()
                          ->where('id', $workspaceItem->shareid)
                          ->where('component', anderspink_articles::get_resource_type());

            if (empty($share) || !$share->exists()) {
                continue;
            }

            $share = $share->one();

            /** @var engage_resource $engageResource */
            $engageResource = engage_resource::repository()->find($share->itemid);
            $article        = entity::repository()->where('id', $engageResource->instanceid)->where('sync', 1);

            if (empty($article) || !$article->exists()) {
                continue;
            }

            $article                  = $article->one();
            $anderspinkBrigedArticles = anderspink_bridged_articles::repository()
                                                                   ->where('article', $article->id)
                                                                   ->where('bridge', $bridgeId);

            if (!empty($anderspinkBrigedArticles) && $anderspinkBrigedArticles->exists()) {
                $shares[] = (object)[
                    'shareid'     => $workspaceItem->shareid,
                    'articleid' => $article->id,
                    'timecreated' => $share->timecreated,
                ];
            }
        }

        return $shares;
    }

    /**
     * @param  int  $workspaceId
     * @param  int  $bridgeId
     * @param  int  $articleId
     *
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function share_exsists(int $workspaceId, int $bridgeId, int $articleId): bool
    {
        $shares = self::get_anderspink_article_in_workspace($workspaceId, $bridgeId);

        foreach ($shares as $share) {
            if ($share->articleid === $articleId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for any content interaction by a user.
     *
     * @param  int  $itemId
     * @param  array  $area
     * @param  string  $component
     *
     * @return bool
     */
    public static function check_for_interactions(
        int $itemId,
        array $area,
        string $component = 'engage_anderspink'
    ): bool {
        //Check for likes
        $likes = builder::table('reaction')
                        ->where('area', $area['reaction'])
                        ->where('component', $component)
                        ->where('instanceid', $itemId);

        if (!empty($likes) && $likes->exists()) {
            return true;
        }

        //Check for Comments
        $comments = builder::table('totara_comment')
                           ->where('area', $area['comment'])
                           ->where('component', $component)
                           ->where('instanceid', $itemId);

        if (!empty($comments) && $comments->exists()) {
            return true;
        }

        return false;
    }
}
