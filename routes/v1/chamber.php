<?php

$router->get('/cek_login', [ 'as' => 'cek_login', 'uses' => 'ChamberController@cek_login']);
$router->get('/menu', [ 'as' => 'menu', 'uses' => 'ChamberController@menu']);
$router->get('/tableRegion', [ 'as' => 'tableRegion', 'uses' => 'ChamberController@tableRegion']);
$router->get('/columnName', [ 'as' => 'columnName', 'uses' => 'ChamberController@columnName']);
$router->get('/hitungRetrieve', [ 'as' => 'hitungRetrieve', 'uses' => 'ChamberController@hitungRetrieve']);
$router->get('/ajaxMsisdn', [ 'as' => 'ajaxMsisdn', 'uses' => 'ChamberController@ajaxMsisdn']);
$router->post('/saveListMsisdn', [ 'as' => 'saveListMsisdn', 'uses' => 'ChamberController@saveListMsisdn']);
$router->post('/updateListMsisdn', [ 'as' => 'updateListMsisdn', 'uses' => 'ChamberController@updateListMsisdn']);
$router->post('/export_mapping_msisdn', [ 'as' => 'export_mapping_msisdn', 'uses' => 'ChamberController@export_mapping_msisdn']);
$router->get('/list_cluster', [ 'as' => 'list_cluster', 'uses' => 'ChamberController@list_cluster']);
$router->get('/list_wlupload', [ 'as' => 'list_wlupload', 'uses' => 'ChamberController@list_wlupload']);
$router->get('/list_achive_top10', [ 'as' => 'list_achive_top10', 'uses' => 'ChamberController@list_achive_top10']);
$router->post('/insert_upload_wl', [ 'as' => 'insert_upload_wl', 'uses' => 'ChamberController@insert_upload_wl']);
$router->post('/export_achiev_wl', [ 'as' => 'export_achiev_wl', 'uses' => 'ChamberController@export_achiev_wl']);
$router->post('/tot_info_achiev', [ 'as' => 'tot_info_achiev', 'uses' => 'ChamberController@tot_info_achiev']);
$router->post('/users_branch_cluster', [ 'as' => 'users_branch_cluster', 'uses' => 'ChamberController@users_branch_cluster']);
$router->get('/list_achive_top10_wabranch', [ 'as' => 'list_achive_top10_wabranch', 'uses' => 'ChamberController@list_achive_top10_wabranch']);
$router->get('/list_achive_top10_wabranch', [ 'as' => 'list_achive_top10_wabranch', 'uses' => 'ChamberController@list_achive_top10_wabranch']);
$router->post('/export_achiev_wabranch', [ 'as' => 'export_achiev_wabranch', 'uses' => 'ChamberController@export_achiev_wabranch']);
$router->get('/tot_info_achiev_wabranch', [ 'as' => 'tot_info_achiev_wabranch', 'uses' => 'ChamberController@tot_info_achiev_wabranch']);
$router->get('/export_achiev', [ 'as' => 'export_achiev', 'uses' => 'ChamberController@export_achiev']);
$router->get('/obc_per_cluster_achiev', [ 'as' => 'obc_per_cluster_achiev', 'uses' => 'ChamberController@obc_per_cluster_achiev']);
$router->post('/upload_file_wabranch', [ 'as' => 'upload_file_wabranch', 'uses' => 'ChamberController@upload_file_wabranch']);
$router->post('/stock_wl_recap', [ 'as' => 'stock_wl_recap', 'uses' => 'ChamberController@stock_wl_recap']);
$router->post('/save_adm_menu', [ 'as' => 'save_adm_menu', 'uses' => 'ChamberController@save_adm_menu']);
$router->put('/update_adm_menu/{id}', [ 'as' => 'update_adm_menu', 'uses' => 'ChamberController@update_adm_menu']);
$router->delete('/delete_adm_menu/{id}', [ 'as' => 'delete_adm_menu', 'uses' => 'ChamberController@delete_adm_menu']);
$router->post('/save_adm_loader', [ 'as' => 'save_adm_loader', 'uses' => 'ChamberController@save_adm_loader']);
$router->put('/update_adm_loader/{id}', [ 'as' => 'update_adm_loader', 'uses' => 'ChamberController@update_adm_loader']);
$router->delete('/delete_adm_loader/{id}', [ 'as' => 'delete_adm_loader', 'uses' => 'ChamberController@delete_adm_loader']);
$router->get('/boopati_loader', [ 'as' => 'boopati_loader', 'uses' => 'ChamberController@boopati_loader']);
