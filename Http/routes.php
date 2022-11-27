<?php

$NS = MODULES_NS.'Importer\Http\Controllers\\';

$router->name('importers.')->group(function () use ($router, $NS) {

    $router->post('importers/fromfile', $NS.'ImporterController@importFromFile');

});

$router->resource('importers', $NS.'ImporterController');
