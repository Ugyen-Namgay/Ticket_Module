<?php
require_once "utils/dbconnect.php";

function raise_error($message) {
    http_response_code(500);
    return '{"error":"'.$message.'"}';
}
function success() {
    return '{"error":false}';
}


global $cache;
global $conn;
$conn = new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
$cache= new Memcached('persistent');
if( !count($cache->getServerList()))
{
    $cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
    $cache->setOption(Memcached::OPT_TCP_NODELAY, true);
    $cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 500);
    $cache->setOption(Memcached::OPT_COMPRESSION, false);
    $cache->addServer('127.0.0.1',11211);
}

function get_cache($key) {
    //echo "CACHED: $key<BR>";
    global $cache;
    $cachedata = $cache->get(crc32($key));
    if ($cachedata) {
        return $cachedata;
    }
    else {
        return false;
    }
}

function clear_cache($key) {
    //echo "CLEARING: $key<BR>";
    global $cache;
    //echo "BEFORE CACHED: ".(string)$cache->get(crc32($key))."<BR>";
    $cache->delete(crc32($key));
    //echo "AFTER CACHED: ".(string)$cache->get(crc32($key))."<BR>";
}

function set_cache($key,$data,$duration=600) {
    global $cache;
    $cache->set(crc32($key),$data,$duration);
}

function get($table,$col="*",$condition="1",$cached = false) {
    global $conn;
    $cacheresult = get_cache($table.$col.$condition);
    if ($cacheresult && $cached) {
        return $cacheresult;
    }
    if ($col=="")
        $col="*";
    
    //echo "SELECT $col FROM $table WHERE $condition;";
    $r=$conn->query("SELECT $col FROM $table WHERE $condition;");
    //echo "SELECT $col FROM $table WHERE $condition;";
    if (!empty($r) && $r->num_rows>0) {
        $returnvalue = json_encode($conn->query("SELECT $col FROM $table WHERE $condition;")->fetch_all(MYSQLI_ASSOC));
        if ($cached) {
            set_cache($table.$col.$condition,$returnvalue);
        }
        return $returnvalue;
    }
    return "[]";
    //$conn->close();
}

function update($table,$col,$val,$condition) {
    global $conn;
    clear_cache($table.$col.$condition);
    clear_cache($table."*".$condition);
    $col=explode(",",$col);
    $val=explode(",",$val);
    if (count($col)!==count($val)) {
        return raise_error("Invalid Size");
    }

    $tempq="";
    for ($i=0;$i<count($col);$i++) {
        $tempq.=$col[$i]."='".$val[$i]."',";
    }

    $tempq=rtrim($tempq,",");

    if (!$conn->query("UPDATE $table SET $tempq WHERE $condition;")) {
        return raise_error("Could not update. Check data: "."UPDATE $table SET $tempq WHERE $condition;");
    }
    else {
        return success();
    }

}

function insert($table,$col,$val) {
    global $conn;
    $col=explode(",",str_replace("`","-",str_replace("'","",str_replace('"','',$col))));
    $val=explode(",",str_replace("`","-",str_replace("'","",str_replace('"','',$val))));

    if (count($col)!=count($val)) {
        return raise_error("Invalid Data size");
    }

    for ($i=0; $i<count($val);$i++) {
        if (strpos($val[$i],"()")===false) {
            $val[$i]="'".$val[$i]."'";
        }
        else {
            $val[$i]=$val[$i];
        }
       
    }

    $tempv=implode(",",$val);
    $col=implode(",",$col);
    //echo "INSERT INTO $table ($col) VALUES($tempv);";
    if (!$conn->query("INSERT INTO $table ($col) VALUES($tempv);")) {
        return raise_error("Could not Insert. Check params => INSERT INTO $table ($col) VALUES($tempv);");
    }
    else {
        return success();
    }

}

function delete($table,$condition) {
    global $conn;
    clear_cache($table."*".$condition);

    if (!$conn->query("DELETE FROM $table WHERE $condition;")) {
        return raise_error("Could not Delete");
    }
    else {
        return success();
    }

}


function isonline() {
    if (!session_id()) {
        return False;
    }
    $user = json_decode(get("admin_user","email,name","session_id='".session_id()."'"),true);
    //exit();
    if (empty($user) || count($user)==0) {
        return False;
    }

    return $user[0]["name"];
}


function deduce($json_data) {
    $data = json_decode($json_data);
    $cols="";
    $vals="";
    foreach ($data as $k=>$v) {
        $cols.=$k.",";
        $vals.=$v.","; 
    }
    $cols=trim($cols,",");
    $vals=trim($vals,",");

    return [$cols,$vals];
}

function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
 }
