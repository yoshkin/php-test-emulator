<?php

namespace AYashenkov\Services;
/**
 * IoC Dependency Injector
 */
$injector = new \Auryn\Injector;
try {
    $injector->alias('Http\Request', 'Http\HttpRequest');
    $injector->share('Http\HttpRequest');
    $injector->define('Http\HttpRequest', [
        ':get' => $_GET,
        ':post' => $_POST,
        ':cookies' => $_COOKIE,
        ':files' => $_FILES,
        ':server' => $_SERVER,
    ]);
    $injector->alias('Http\Response', 'Http\HttpResponse');
    $injector->share('Http\HttpResponse');
    $injector->alias('AYashenkov\Services\IRenderer', 'AYashenkov\Services\TwigRenderer');
    $injector->delegate('Twig_Environment', function () use ($injector) {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/Views');
        $twig = new \Twig_Environment($loader);
        return $twig;
    });
} catch (\Auryn\ConfigException $exception) {
    echo $exception->getMessage();
}
return $injector;