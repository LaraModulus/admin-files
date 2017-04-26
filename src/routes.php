<?php
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin/files',
    'middleware' => ['admin', 'auth.admin'],
    'namespace' => 'LaraMod\Admin\Files\Controllers',
], function () {
    Route::get('/', ['as' => 'admin.files', 'uses' => 'FilesController@index']);

    Route::get('download', ['as' => 'admin.files.download', 'uses' => 'FilesController@downloadFile']);

    Route::group([
        'prefix' => 'api',
        'middleware' => 'auth.admin',
        'namespace' => 'Api'
    ], function(){
       Route::get('directories', ['as' => 'admin.api.directories', 'uses' => 'DirectoriesController@index']);
        Route::group(['prefix' => 'files'], function(){
            Route::get('/', ['as' => 'admin.api.files', 'uses' => 'FilesController@index']);
            Route::post('/', ['as' => 'admin.api.files', 'uses' => 'FilesController@postForm']);
            Route::get('/delete', ['as' => 'admin.api.files.delete', 'uses' => 'FilesController@delete']);
        });


       Route::get('sync', ['as' => 'admin.api.files.sync', 'uses' => 'DirectoriesController@sync']);
    });
});