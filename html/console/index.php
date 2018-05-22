<?php 
require '../../vendor/autoload.php';
$app = new Silex\Application();

$app->get('/login', function() use($app) {
    return "faça login";
});

$app->get('/panel', function() use($app) {
    return "dashboard";
});

$app->get('/logout', function() use($app) {
    return "sair";
});

$app->run();