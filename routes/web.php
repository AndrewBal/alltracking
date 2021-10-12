<?php

use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', 'Callbacks\SiteMapController@generate');
Route::get('/sitemap-{index?}.xml', 'Callbacks\SiteMapController@generate');

Route::get('/login', [
    'as'   => 'login',
    'uses' => 'Auth\LoginController@showLoginForm'
]);
Route::post('/login', [
    'as'   => 'login',
    'uses' => 'Auth\LoginController@login'
]);
Route::match([
    'get',
    'post'
], '/logout', [
    'as'   => 'logout',
    'uses' => 'Auth\LoginController@logout'
]);

Route::match([
    'get',
    'post'
], '/', [
    'as'   => 'home',
    'uses' => 'FrontController@home'
]);

Route::get('/tracking-packages/{package?}', [
    'as'   => 'tracking_packages',
    'uses' => 'PackagesController@index'
]);

Route::post('/set-tracking', [
    'as'   => 'set_tracking_packages',
    'uses' => 'PackagesController@setPackage'
]);

Route::match([
    'get',
    'post'
], '/{path}', [
    'as'   => 'path',
    'uses' => 'FrontController@path'
])->where([
    'path' => '^(?!oleus|ajax).*?'
]);
