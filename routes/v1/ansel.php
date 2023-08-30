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
    $router->post('/save_update_project/{id}', [ 'as' => 'save_update_project', 'uses' => 'AnselController@save_update_project']);
    $router->post('/save_update_project', [ 'as' => 'save_update_project', 'uses' => 'AnselController@save_update_project']);
    $router->get('/list_user_dropdown/{id}', [ 'as' => 'list_user_dropdown', 'uses' => 'AnselController@list_user_dropdown']);
    $router->post('/add_user', [ 'as' => 'add_user', 'uses' => 'AnselController@add_user']);
    $router->get('/list_undian/{id}', [ 'as' => 'list_undian', 'uses' => 'AnselController@list_undian']);
    $router->get('/valid_project', [ 'as' => 'valid_project', 'uses' => 'AnselController@valid_project']);
    $router->get('/list_hadiah_undi/{id}', [ 'as' => 'list_hadiah_undi', 'uses' => 'AnselController@list_hadiah_undi']);
    $router->get('/angka_jumlah/{id}', [ 'as' => 'angka_jumlah', 'uses' => 'AnselController@angka_jumlah']);
    $router->post('/undi_acak_peserta', [ 'as' => 'undi_acak_peserta', 'uses' => 'AnselController@undi_acak_peserta']);
    $router->post('/undi_get_pemenang', [ 'as' => 'undi_get_pemenang', 'uses' => 'AnselController@undi_get_pemenang']);
    $router->post('/undi_get_peserta', [ 'as' => 'undi_get_peserta', 'uses' => 'AnselController@undi_get_peserta']);
    $router->delete('/pemenang_delete_all/{id}', [ 'as' => 'pemenang_delete_all', 'uses' => 'AnselController@pemenang_delete_all']);
    $router->put('/pemenang_delete_satu/{id}', [ 'as' => 'pemenang_delete_satu', 'uses' => 'AnselController@pemenang_delete_satu']);
    $router->get('/field_list_pemenang/{id}', [ 'as' => 'field_list_pemenang', 'uses' => 'AnselController@field_list_pemenang']);
    $router->get('/export_pemenang/{id}', [ 'as' => 'export_pemenang', 'uses' => 'AnselController@export_pemenang']);

});


