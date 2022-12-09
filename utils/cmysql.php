<?php
global $cache;
$settings = parse_ini_file("settings/config.ini", true);
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

/* INITIALIZATION */
$has_cached = $cache->get("tables");
if (!$has_cached) {
    $tables = $conn->query("SHOW TABLES;")->fetch_all(MYSQLI_ASSOC);
    foreach ($tables as $table) {
        $table_content = $conn->query("SELECT * FROM ".$table["Tables_in_ticket_module"].";")->fetch_all(MYSQLI_ASSOC);
        $cache->set("table_".$table["Tables_in_ticket_module"],json_encode($table_content),0);
    }
}

$query_q = $cache->get("QUERYQ");
if (!$query_q) {
    $cache->set("QUERYQ",[],0);
}

//echo $cache->get('table_admin_user');

function cget($table,$field="*",$condition="*") {
    $tempt = json_decode(get("table_".$table),true);
    $conditionfield = explode("=",$condition)[0];
    $conditionvalue = explode("=",$condition)[1];
    $returnvalue=false;
    foreach ($tempt as $rows) {
        if ($rows[$conditionfield]=$conditionvalue) {
            $returnvalue=$rows[$field];
            break;
        }
    }
    return $returnvalue;
}

//cget("admin_user","name","id=1");