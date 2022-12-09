<?php
include 'Router.php';
session_start();
$request = $_SERVER['REQUEST_URI'];
$router = new Router($request);
$router->get('/', 'app/landing');
$router->get('home', 'app/home');
$router->get('users', 'app/users');
$router->get('events', 'app/events');
$router->get('submit', 'utils/submission');

$router->get('test', 'utils/cachemodule');
$router->get('plugin', 'utils/cmysql');
$router->get('adminapp', 'user/adminapp');


$router->get('register', 'user/register');
$router->get('singleregister', 'user/singleregister');
$router->get('check', 'user/admin');
$router->get('login','user/login');
$router->get('dashboard','app/dashboard');


$router->get('luckydraw', 'user/luckydraw');
$router->get('luckydrawsettings', 'user/luckydrawsettings');
$router->get('ndapi', 'utils/nationaldayapi');

$router->get('luckydrawevent','utils/luckydraw');
$router->get('eventluckydraw','user/eventluckydraw');
$router->get('eventluckydrawsettings','user/eventluckydrawsettings');

$router->get('error-401','errorpage/error-401');
$router->get('error-404','errorpage/error-404');
$router->get('error-403','errorpage/error-403-invalid');
$router->get('error-500','errorpage/error-500');