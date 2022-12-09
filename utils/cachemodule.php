<?php
$domain="http://43.230.208.133";
$domain="http://43.230.208.133/cidapi.php?cid=11512005551&token=gG3eFFRuPrVW4KH26aTj&podo";
echo crc32("checkOnlinehttp://43.230.208.133/vehicleapi.php?cid=11512005551&token=By3CC2B8Z3ee8FvzmGPf");
$curlInit = curl_init($domain);
curl_setopt($curlInit,CURLOPT_HEADER,false);
curl_setopt($curlInit,CURLOPT_TIMEOUT_MS,2000);
//curl_setopt($curlInit,CURLOPT_NOBODY,true);
curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

//get answer
$response = curl_exec($curlInit);
//$response = $domain;

curl_close($curlInit);

var_dump($response);

//require_once "utils/dbconnect.php";
// $cache = new Memcached('persistent');
// if( !count($cache->getServerList()))
// {
//     $cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
//     $cache->setOption(Memcached::OPT_TCP_NODELAY, true);
//     $cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 500);
//     $cache->setOption(Memcached::OPT_COMPRESSION, false);
//     $cache->addServer('127.0.0.1',11211);
// }

// //$loaded = $cache->get("loaded");
// $loaded = false;
// if (!$loaded) {
//     $conn = new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
//     $r=$conn->query("SHOW TABLES;");
//     if (!empty($r) && $r->num_rows>0) {
//         $prefix="TABLE_";
//         if ($r->num_rows>1000) {
//             $prefix="TABLE_HEAVY_";
//         }
//         while ($table = $r->fetch_assoc()) {
//             $db_table = $table["Tables_in_".DB_NAME];
//             $rows = $conn->query("SELECT * FROM ".$db_table);
//             $records = $rows->fetch_all(MYSQLI_ASSOC);
//             $cache->set($prefix.$db_table,$records,0);
//         }
//     }
//     $cache->set("loaded","Yes",0);
// }

// function cget($table,$column,$conditions) {
//     $conditionset = explode(",",$conditions);
//     $cache = new Memcached('persistent');
//     $loadtable = $cache->get("TABLE_".$table);
//     if ($loadtable) {
//         $loadtable = $cache->get("TABLE_HEAVY_".$table);
//     }
//     $result=[];
//     foreach ($loadtable as $rec) {
//         $found==1;
//         foreach ($conditionset as $cond) {
//             $key = explode("=",$cond)[0];
//             $val = explode("=",$cond)[1];
            
//             if ($rec[$key]==$val) {
//                 $found++;
//             }
//         }
//         if ($found == count($conditionset)) {
//             if ($column=="*") {
//                 $result[] = $rec;
//             }
//             else {
//                 $requiredcols = explode(",",$column);
//                 $temp_rec = [];
//                 foreach ($requiredcols as $keys) {
//                     $temp_rec[]=$rec[$keys];
//                 }
//                 $result[] = $temp_rec;
//             }
//         }
//     }

//     print_r($result);
    
// }

// $cache_db = new SQLite3(':memory:');
// // $cache_db = new SQLite3('file::memory:?cache=shared');

// $cache_db->query("BEGIN TRANSACTION;");


// $conn = new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
// $r=$conn->query("SHOW TABLES;");
// if (!empty($r) && $r->num_rows>0) {
//     while ($table = $r->fetch_assoc()) {
//         $db_table = $table["Tables_in_".DB_NAME];
//         $create_script = $conn->query("SHOW CREATE TABLE ".$db_table);
//         $sqliteprerequisite = $create_script->fetch_assoc()["Create Table"];
//         $sqliteprerequisite = preg_replace("/ AUTO_INCREMENT=[0-9]/i", "", $sqliteprerequisite);
//         $sqliteprerequisite = str_replace("ENGINE=InnoDB DEFAULT CHARSET=utf8mb4","",$sqliteprerequisite);
//         $sqliteprerequisite = str_replace("AUTO_INCREMENT","",$sqliteprerequisite);
//         $sqliteprerequisite = preg_replace("/\(([0-9]||[0-9][0-9]||[0-9][0-9][0-9])\)/i", "", $sqliteprerequisite);
//         $sqliteprerequisite = str_replace("varchar", "TEXT", $sqliteprerequisite);
//         $sqliteprerequisite = str_replace("int", "INTEGER", $sqliteprerequisite);
//         $sqliteprerequisite = str_replace("date","datetime",$sqliteprerequisite);
//         $cache_db->exec($sqliteprerequisite);
//         $rows = $conn->query("SELECT * FROM ".$db_table);
//         $records = $rows->fetch_all(MYSQLI_ASSOC);
//         foreach ($records as $rec) {
//             $statement = "INSERT INTO `$db_table` (".implode(",",array_keys($rec)).") VALUES('".implode("','",array_values($rec))."');";
//             $cache_db->exec($statement );
//         }   
        

//     }
// }
// $cache_db->query("COMMIT;");


// $stmt = $cache_db->query("SELECT * FROM `admin_user` WHERE NOT cid='11512005551';");
// $results = $stmt->fetchArray(SQLITE3_ASSOC);
// print_r($results);



?>