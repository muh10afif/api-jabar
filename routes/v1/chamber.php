<?php

$router->get('/cek_login', [ 'as' => 'cek_login', 'uses' => 'ChamberController@cek_login']);
$router->get('/menu', [ 'as' => 'menu', 'uses' => 'ChamberController@menu']);
$router->get('/tableRegion', [ 'as' => 'tableRegion', 'uses' => 'ChamberController@tableRegion']);
$router->get('/columnName', [ 'as' => 'columnName', 'uses' => 'ChamberController@columnName']);
$router->get('/hitungRetrieve', [ 'as' => 'hitungRetrieve', 'uses' => 'ChamberController@hitungRetrieve']);
$router->get('/ajaxMsisdn', [ 'as' => 'ajaxMsisdn', 'uses' => 'ChamberController@ajaxMsisdn']);
$router->post('/saveListMsisdn', [ 'as' => 'saveListMsisdn', 'uses' => 'ChamberController@saveListMsisdn']);
$router->post('/updateListMsisdn', [ 'as' => 'updateListMsisdn', 'uses' => 'ChamberController@updateListMsisdn']);
