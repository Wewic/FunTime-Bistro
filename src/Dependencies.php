<?php declare(strict_types = 1);

$injector = new \Auryn\Injector;

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

$injector->alias('ProjectFunTime\Template\Renderer', 'ProjectFunTime\Template\TwigRenderer');
$injector->delegate('Twig_Environment', function () use ($injector) {
   $loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/templates');
   $twig = new Twig_Environment($loader);
   return $twig;
});

$injector->alias('ProjectFunTime\Template\FrontendRenderer', 'ProjectFunTime\Template\FrontendTwigRenderer');

$injector->alias('ProjectFunTime\Menu\MenuReader', 'ProjectFunTime\Menu\ArrayMenuReader');
$injector->share('ProjectFunTime\Menu\ArrayMenuReader');

$injector->alias('ProjectFunTime\Database\DatabaseProvider', 'ProjectFunTime\Database\MySQLDatabaseProvider');
$injector->share('ProjectFunTime\Database\MySQLDatabaseProvider');

$injector->alias('ProjectFunTime\Session\SessionWrapper', 'ProjectFunTime\Session\PHPSessionWrapper');

return $injector;