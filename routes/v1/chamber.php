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
