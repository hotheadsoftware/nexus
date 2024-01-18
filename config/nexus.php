<?php

return [
    'account' => [
        'user' => [
            'name'     => env('ACCOUNT_PANEL_USER_NAME', ''),
            'email'    => env('ACCOUNT_PANEL_USER_EMAIL', ''),
            'password' => env('ACCOUNT_PANEL_USER_PASSWORD', ''),
        ],
    ],

    'operate' => [
        'user' => [
            'name'     => env('OPERATE_PANEL_USER_NAME', ''),
            'email'    => env('OPERATE_PANEL_USER_EMAIL', ''),
            'password' => env('OPERATE_PANEL_USER_PASSWORD', ''),
        ],
    ],

    'admin' => [
        'user' => [
            'name'     => env('ADMIN_PANEL_USER_NAME', ''),
            'email'    => env('ADMIN_PANEL_USER_EMAIL', ''),
            'password' => env('ADMIN_PANEL_USER_PASSWORD', ''),
        ],
    ],
];
