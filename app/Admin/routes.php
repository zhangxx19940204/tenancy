<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('houses', Rent\HouseController::class);
    $router->resource('tenants', Rent\TenantController::class);
    $router->resource('revenue-expenses', Rent\RevenueExpensesController::class);
    $router->resource('projects', Rent\ProjectController::class);


});

