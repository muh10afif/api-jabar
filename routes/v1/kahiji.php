<?php

$router->get('/zplchartdirect', [ 'as' => 'zplchartdirect', 'uses' => 'KahijiController@zplchartdirect']);
$router->get('/packetlossperhubmetro', [ 'as' => 'packetlossperhubmetro', 'uses' => 'KahijiController@packetlossperhubmetro']);
$router->get('/hourlymonitoringpacketloss', [ 'as' => 'hourlymonitoringpacketloss', 'uses' => 'KahijiController@hourlymonitoringpacketloss']);
$router->get('/alarmdown', [ 'as' => 'alarmdown', 'uses' => 'KahijiController@alarmdown']);
$router->get('/alarmeas', [ 'as' => 'alarmeas', 'uses' => 'KahijiController@alarmeas']);
$router->get('/alarmlocked', [ 'as' => 'alarmlocked', 'uses' => 'KahijiController@alarmlocked']);
$router->get('/dapotranneweekly', [ 'as' => 'dapotranneweekly', 'uses' => 'KahijiController@dapotranneweekly']);
$router->get('/avaweeklyresume', [ 'as' => 'avaweeklyresume', 'uses' => 'KahijiController@avaweeklyresume']);
$router->get('/dapotrannemonthly', [ 'as' => 'dapotrannemonthly', 'uses' => 'KahijiController@dapotrannemonthly']);
