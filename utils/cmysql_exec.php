<?php
global $cache;
$settings = parse_ini_file("/var/www/html/ticket/Ticket_Module/settings/config.ini", true);
global $conn;
$conn = new mysqli($settings["db"]["url"],$settings["db"]["username"],$settings["db"]["password"],$settings["db"]["database"]);
$cache= new Memcached('persistent');
if( !count($cache->getServerList()))
{
    $cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
    $cache->setOption(Memcached::OPT_TCP_NODELAY, true);
    $cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 500);
    $cache->setOption(Memcached::OPT_COMPRESSION, false);
    $cache->addServer('127.0.0.1',11211);
}

$queryCache=$cache->get("SQLQ");
$cache->replace("SQLQ",[]);
if ($queryCache) {
    foreach ($queryCache as $query) {
        $conn->query($query);
    }
}
$conn->close();
$cache->quit();
