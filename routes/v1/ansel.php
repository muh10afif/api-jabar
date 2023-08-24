<?php

$router->group(['prefix' => 'ansel', 'as' => 'ansel'], function () use ($router) {

    $router->get('/cek_login', [ 'as' => 'cek_login', 'uses' => 'AnselController@cek_login']);
    $router->delete('/delete_project/{id}', [ 'as' => 'delete_project', 'uses' => 'AnselController@delete_project']);
    $router->get('/list_master', [ 'as' => 'list_master', 'uses' => 'AnselController@list_master']);
    $router->post('/list_configure', [ 'as' => 'list_configure', 'uses' => 'AnselController@list_configure']);
    $router->post('/list_hadiah', [ 'as' => 'list_hadiah', 'uses' => 'AnselController@list_hadiah']);
    $router->post('/list_peserta', [ 'as' => 'list_peserta', 'uses' => 'AnselController@list_peserta']);
    $router->post('/project_exist', [ 'as' => 'project_exist', 'uses' => 'AnselController@project_exist']);
    $router->get('/list_project_edit/{id}', [ 'as' => 'list_project_edit', 'uses' => 'AnselController@list_project_edit']);
    $router->put('/save_update_project/{id}', [ 'as' => 'save_update_project', 'uses' => 'AnselController@save_update_project']);

});


