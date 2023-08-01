<?php

$router->get('/rabfail', [ 'as' => 'rabfail', 'uses' => 'RabfailController@index']);
$router->get('/triDay', [ 'as' => 'triDay', 'uses' => 'RabfailController@triDay']);
$router->post('/listRtp', [ 'as' => 'listRtp', 'uses' => 'RabfailController@listRtp']);
$router->post('/list50', [ 'as' => 'list50', 'uses' => 'RabfailController@list50']);
$router->post('/exportRabfail', [ 'as' => 'exportRabfail', 'uses' => 'RabfailController@exportRabfail']);
$router->get('/detailRTP', [ 'as' => 'detailRTP', 'uses' => 'RabfailController@detailRTP']);

