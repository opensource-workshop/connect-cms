<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    |
    | Connect-CMS role const
    |
    */

    'ROLE_SYSTEM_MANAGER'    => 1,  // システム管理者
    'ROLE_SITE_MANAGER'      => 2,  // サイト管理者
    'ROLE_USER_MANAGER'      => 3,  // ユーザ管理者
    'ROLE_PAGE_MANAGER'      => 4,  // ページ管理者
    'ROLE_OPERATION_MANAGER' => 5,  // 運用管理者
    'ROLE_APPROVER'          => 10, // 承認者
    'ROLE_EDITOR'            => 11, // 編集者
    'ROLE_MODERATOR'         => 12, // モデレータ
    'ROLE_GENERAL'           => 13, // 一般
    'ROLE_GUEST'             => 0,  // ゲスト

    /*
    |--------------------------------------------------------------------------
    | Authority
    |--------------------------------------------------------------------------
    |
    | Connect-CMS authority const
    |
    */

    'CC_AUTHORITY' => [

        // フレーム
        'frames.create'   => ['role_arrangement', 'role_article_admin'],
        'frames.move'     => ['role_arrangement', 'role_article_admin'],
        'frames.edit'     => ['role_arrangement', 'role_article_admin'],
        'frames.delete'   => ['role_arrangement', 'role_article_admin'],
        'frames.change'   => ['role_arrangement', 'role_article_admin'],

        // バケツ
        'buckets.create'  => ['role_arrangement', 'role_article_admin'],

        // 記事
        'posts.create'   => ['role_reporter', 'role_article', 'role_article_admin'],
        'posts.update'   => ['role_reporter', 'role_article', 'role_article_admin'],
        'posts.delete'   => ['role_reporter', 'role_article', 'role_article_admin'],
        'posts.approval' => ['role_approval', 'role_article_admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Method Authority Check
    |--------------------------------------------------------------------------
    |
    | Connect-CMS method authority check const
    |
    */

    'CC_METHOD_AUTHORITY' => [

        // 記事（複数権限が指定されている場合は、アンド条件）
        'create'         => ['posts.create'],
        'edit'           => ['posts.update'],
        'store'          => ['posts.create'],
        'update'         => ['posts.update'],
        'save'           => ['posts.create', 'posts.update'],
        'temporarysave'  => ['posts.create', 'posts.update'],
        'delete'         => ['posts.delete'],
        'destroy'        => ['posts.delete'],
        'listBuckets'    => ['frames.change'],
        'createBuckets'  => ['frames.create'],
        'editBuckets'    => ['frames.edit'],
        'saveBuckets'    => ['frames.create'],
        'destroyBuckets' => ['frames.delete'],
        'changeBuckets'  => ['frames.change'],
    ],

];
