<?php

use Illuminate\Support\Facades\Route;

Route::post('/file-upload', [
    'as' => 'ajax.file.upload',
    'uses' => 'FileController@upload'
]);

Route::post('/file-update/{file}', [
    'as' => 'ajax.file.update',
    'uses' => 'FileController@update'
]);

Route::post('/file-sort', [
    'as' => 'ajax.file.sort',
    'uses' => 'FileController@sort'
]);

Route::post('/shortcut', [
    'as' => 'ajax.shortcut',
    'uses' => 'ShortCutController@index'
]);

Route::post('/checked-reCaptcha', [
    'as' => 'ajax.validate_reCaptcha',
    'uses' => 'ReCaptchaController@load'
]);

Route::post('/open-form/{form}', [
    'as' => 'ajax.open_form',
    'uses' => 'FormController@open_form'
]);

Route::post('/submit-form/{form}', [
    'as' => 'ajax.submit_form',
    'uses' => 'FormController@submit_form'
]);

Route::match(['get', 'post'], '/get-data-for-package/{package?}', [
    'as' => 'ajax.get_data_for_package',
    'uses' => 'PackageController@get_data'
]);
