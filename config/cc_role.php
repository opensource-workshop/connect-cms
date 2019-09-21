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
        // バケツ
        'buckets.create'   => ['role_arrangement', 'role_article_admin', 'admin_system'],

        // 記事
        'posts.create'   => ['role_reporter', 'role_article', 'admin_system'],
        'posts.update'   => ['role_reporter', 'role_article', 'admin_system'],
        'posts.delete'   => ['role_reporter', 'role_article', 'admin_system'],
        'posts.approval' => ['role_approval', 'admin_system'],
    ],

];
