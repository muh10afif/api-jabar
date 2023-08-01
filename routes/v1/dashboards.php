<?php

$router->get('/scr_mss', [ 'as' => 'scr_mss', 'uses' => 'DashboardsController@scr_mss']);
$router->get('/pdp_sr', [ 'as' => 'pdp_sr', 'uses' => 'DashboardsController@pdp_sr']);
$router->get('/scr_ccr_graph', [ 'as' => 'scr_ccr_graph', 'uses' => 'DashboardsController@scr_ccr_graph']);
$router->get('/pdp_sr_graph', [ 'as' => 'pdp_sr_graph', 'uses' => 'DashboardsController@pdp_sr_graph']);
$router->get('/ggsn', [ 'as' => 'ggsn', 'uses' => 'DashboardsController@ggsn']);
$router->get('/ggsn_fan_temp', [ 'as' => 'ggsn_fan_temperature', 'uses' => 'DashboardsController@ggsn_fan_temperature']);
$router->post('/ggsn_dropdown', [ 'as' => 'ggsn_dropdown', 'uses' => 'DashboardsController@ggsn_dropdown']);
