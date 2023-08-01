<?php
use App\Libraries\Core;

/* v1 group */
$router->group(['prefix' => 'api/v1', 'as' => 'api/v1'], function () use ($router) {

    Core::renderRoutes('v1', $router);

});
