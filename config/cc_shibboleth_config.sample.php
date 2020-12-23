<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connect-CMS AuthType shibboleth location (mod_shib)
    |--------------------------------------------------------------------------
    |
    | AuthType shibboleth location in apache mod_shib.
    |
    */

    'location' => 'secure',

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
