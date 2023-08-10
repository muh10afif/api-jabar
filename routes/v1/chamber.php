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
