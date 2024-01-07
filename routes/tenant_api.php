<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| You can get an API token from the user interface under your user profile
| page. This token is a long-lived token similar to your Github Personal
| Access Token. You can use this token to authenticate your API calls.
|
| http://localhost/api/user
|
| Headers:
|     Accept: application/json
|     Authorization: Bearer <token>
|
*/

Route::group([
    'middleware' => [
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth:sanctum',
    ],
], function () {

    /*
    |--------------------------------------------------------------------------
    | One Route Group Per Panel
    |--------------------------------------------------------------------------
    |
    | In Nexus, each Panel represents a distinct user type, where each type has
    | its own table, auth guard, routes, interface, needs, etc. This enables
    | you to create a custom experience for each user type in your app.
    |
    | Defining API routes on a per-panel basis allows us to ensure that no user
    | can access an API route or data that they are not authorized to access.
    |
    */

    Route::group([
        'prefix' => 'operator',
    ], function () {

        Route::get('user', function (Request $request) {
            return $request->user();
        });
    });

});
