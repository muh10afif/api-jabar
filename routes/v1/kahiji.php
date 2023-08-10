<?php

$router->get('/zplchartdirect', [ 'as' => 'zplchartdirect', 'uses' => 'KahijiController@zplchartdirect']);
$router->get('/packetlossperhubmetro', [ 'as' => 'packetlossperhubmetro', 'uses' => 'KahijiController@packetlossperhubmetro']);
$router->get('/hourlymonitoringpacketloss', [ 'as' => 'hourlymonitoringpacketloss', 'uses' => 'KahijiController@hourlymonitoringpacketloss']);
$router->get('/alarmeas', [ 'as' => 'alarmeas', 'uses' => 'KahijiController@alarmeas']);
