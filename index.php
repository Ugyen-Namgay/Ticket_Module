<?php
include 'Router.php';
session_start();
$request = $_SERVER['REQUEST_URI'];
$router = new Router($request);
$router->get('/', 'app/landing');

$router->get('error-401','errorpage/error-401');
$router->get('error-404','errorpage/error-404');
$router->get('error-403','errorpage/error-403-invalid');
$router->get('error-500','errorpage/error-500');