<?php

namespace engage_anderspink\totara_engage\interactor;

use coding_exception;
use engage_article\totara_engage\resource\article;
use engage_anderspink\totara_engage\resource\anderspink_articles;
use moodle_exception;
use totara_engage\access\accessible;
use totara_engage\interactor\interactor;

class anderspink_interactor extends interactor {

    /**
     * @param  accessible  $resource
     * @param  int|null  $actor_id
     *
     * @return interactor
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function create_from_accessible(accessible $resource, ?int $actor_id = null): interactor {
        if (!($resource instanceof anderspink_articles)) {
            throw new coding_exception('Invalid accessible resource for anderspink article interactor');
        }

        /** @var anderspink_articles $anderspinkArticle */
        $anderspinkArticle = $resource;

        return new self($anderspinkArticle->to_array(), $actor_id);
    }
}
