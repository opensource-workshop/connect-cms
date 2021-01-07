<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connect-CMS AuthType shibboleth login path (mod_shib)
    |--------------------------------------------------------------------------
    |
    | AuthType shibboleth login path in apache mod_shib.
    |
    */

    'login_path' => 'secure',

    /*
    |--------------------------------------------------------------------------
    | Connect-CMS Shibboleth login user (mod_shib)
    |--------------------------------------------------------------------------
    |
    | Automatic user registration and automatic login from $_SERVER set in apache mod_shib.
    |
    */

    'userid' => 'REDIRECT_mail',
    'user_name' => 'REDIRECT_employeeNumber',
    'user_email' => 'REDIRECT_mail',
];
