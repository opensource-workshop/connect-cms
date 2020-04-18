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
/*
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
*/
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
        'frames.create'              => ['role_arrangement', 'role_article_admin'],
        'frames.move'                => ['role_arrangement', 'role_article_admin'],
        'frames.edit'                => ['role_arrangement', 'role_article_admin'],
        'frames.delete'              => ['role_arrangement', 'role_article_admin'],
        'frames.change'              => ['role_arrangement', 'role_article_admin'],

        // バケツ
        'buckets.create'             => ['role_arrangement', 'role_article_admin'],
        'buckets.delete'             => ['role_arrangement', 'role_article_admin'],
        'buckets.addColumn'          => ['role_arrangement', 'role_article_admin'],
        'buckets.editColumn'         => ['role_arrangement', 'role_article_admin'],
        'buckets.deleteColumn'       => ['role_arrangement', 'role_article_admin'],
        'buckets.reloadColumn'       => ['role_arrangement', 'role_article_admin'],
        'buckets.upColumnSequence'   => ['role_arrangement', 'role_article_admin'],
        'buckets.downColumnSequence' => ['role_arrangement', 'role_article_admin'],
        'buckets.saveColumn'         => ['role_arrangement', 'role_article_admin'],
        'buckets.downloadCsv'        => ['role_arrangement', 'role_article_admin'],

        // 記事(記入の初期値は管理者のみ)
        'posts.create'               => ['role_article_admin'],
        'posts.update'               => ['role_article_admin'],
        'posts.delete'               => ['role_article_admin'],
        'posts.approval'             => ['role_approval', 'role_article_admin'],
/*
        'posts.create'               => ['role_reporter', 'role_article', 'role_article_admin'],
        'posts.update'               => ['role_reporter', 'role_article', 'role_article_admin'],
        'posts.delete'               => ['role_reporter', 'role_article', 'role_article_admin'],
        'posts.approval'             => ['role_approval', 'role_article_admin'],
*/
    ],

    /*
    |--------------------------------------------------------------------------
    | Role List
    |--------------------------------------------------------------------------
    |
    | Connect-CMS Role List const
    |
    */

    'CC_ROLE_LIST' => [

        'role_article_admin' => 'コンテンツ管理者',
        'role_arrangement'   => 'プラグイン管理者',
        'role_article'       => 'モデレータ',
        'role_approval'      => '承認者',
        'role_reporter'      => '編集者',
        'role_guest'         => 'ゲスト',

        'admin_system'       => 'システム管理者',
        'admin_site'         => 'サイト管理者',
        'admin_page'         => 'ページ管理者',
        'admin_user'         => 'ユーザ管理者',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Hierarchy
    |--------------------------------------------------------------------------
    |
    | Connect-CMS Role Hierarchy const
    |
    */

    'CC_ROLE_HIERARCHY' => [

        'role_article_admin' => ['role_article_admin'],
        'role_arrangement'   => ['role_arrangement', 'role_article_admin'],
        'role_reporter'      => ['role_reporter', 'role_article_admin'],
        'role_approval'      => ['role_approval', 'role_article_admin'],
        'role_article'       => ['role_article', 'role_article_admin'],

        'admin_system'       => ['admin_system'],
        'admin_page'         => ['admin_page', 'admin_system'],
        'admin_site'         => ['admin_site', 'admin_system'],
        'admin_user'         => ['admin_user', 'admin_system'],
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
        'create'              => ['posts.create'],
        'edit'                => ['posts.update'],
        'store'               => ['posts.create'],
        'update'              => ['posts.update'],
        'save'                => ['posts.create', 'posts.update'],
        'temporarysave'       => ['posts.create', 'posts.update'],
        'delete'              => ['posts.delete'],
        'destroy'             => ['posts.delete'],
        'approval'            => ['posts.approval'],

        // バケツ＆フレーム
        'listBuckets'         => ['frames.change'],
        'createBuckets'       => ['frames.create'],
        'editBuckets'         => ['frames.edit'],
        'saveBuckets'         => ['frames.create'],
        'destroyBuckets'      => ['frames.delete'],
        'changeBuckets'       => ['frames.change'],
        'editBucketsRoles'    => ['frames.edit'],
        'saveBucketsRoles'    => ['frames.edit'],
        
        'addColumn'           => ['buckets.addColumn'],
        'editColumn'          => ['buckets.editColumn'],
        'deleteColumn'        => ['buckets.deleteColumn'],
        'reloadColumn'        => ['buckets.reloadColumn'],
        'upColumnSequence'    => ['buckets.upColumnSequence'],
        'downColumnSequence'  => ['buckets.downColumnSequence'],
        'saveColumn'          => ['buckets.saveColumn'],
        'downloadCsv'         => ['buckets.downloadCsv'],
    ],

    'CC_METHOD_REQUEST_METHOD' => [

        // 記事（複数権限が指定されている場合は、アンド条件）
        'create'              => ['get'],
        'edit'                => ['get'],
        'show'                => ['get'],
        'store'               => ['post'],
        'update'              => ['post'],
        'save'                => ['post'],
        'temporarysave'       => ['post'],
        'delete'              => ['post'],
        'destroy'             => ['post'],
        'approval'            => ['post'],

        // ゲストでも実行されるメソッド
        'index'               => ['post'],
        'publicConfirm'       => ['post'],
        'publicStore'         => ['post'],

        // バケツ＆フレーム
        'listBuckets'         => ['get'],
        'createBuckets'       => ['get'],
        'editBuckets'         => ['get'],
        'editBucketsRoles'    => ['get'],
        'saveBuckets'         => ['post'],
        'destroyBuckets'      => ['post'],
        'changeBuckets'       => ['post'],
        'saveBucketsRoles'    => ['post'],

        'addColumn'           => ['post'],
        'editColumn'          => ['get'],
        'deleteColumn'        => ['post'],
        'reloadColumn'        => ['post'],
        'upColumnSequence'    => ['post'],
        'downColumnSequence'  => ['post'],
        'saveColumn'          => ['post'],
        'downloadCsv'         => ['post'],
    ],

];
