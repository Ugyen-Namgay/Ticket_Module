<?php
require_once "utils/dbconnect.php";
$cache = new Memcached('persistent');
if( !count($cache->getServerList()))
{
    $cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
    $cache->setOption(Memcached::OPT_TCP_NODELAY, true);
    $cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 500);
    $cache->setOption(Memcached::OPT_COMPRESSION, false);
    $cache->addServer('127.0.0.1',11211);
}

$loaded = $cache->get("loaded");
if (!$loaded) {
    $conn = new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
    $r=$conn->query("SHOW TABLES;");
    if (!empty($r) && $r->num_rows>0) {
        while ($table = $r->fetch_assoc()) {
            $db_table = $table["Tables_in_".DB_NAME];
            $rows = $conn->query("SELECT * FROM ".$db_table);
            $records = $rows->fetch_all();
            $cache->set("TABLE_".$db_table,$records,0);
            print_r($records);
        }
    }
    $cache->set("loaded","Yes",0);

}


