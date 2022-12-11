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


// FROM https://stackoverflow.com/questions/22949597/getting-max-values-in-json-array
function getMax($arr, $prop) {
    $max=0;
    foreach ($arr as $a) {
        if ($max == null || (int)($a[$prop]) > (int)($max))
            $max = $a[$prop];
    }
    
    return $max;
}

/* INITIALIZATION */
$has_cached = $cache->get("tables");
if (!$has_cached) {
    $cache->set("queryCounter","1",10);
    //echo "INITIALIZING DATABASE LOADS<BR>";
    $tables = $conn->query("SHOW TABLES;")->fetch_all(MYSQLI_ASSOC);
    foreach ($tables as $table) {
        $tablename = trim($table["Tables_in_".$settings["db"]["database"]]);
        if ($tablename=="images") {
            //echo "Skipping table: '".$tablename."'<BR>";
            continue;
        }
        //echo "Loading table: '".$tablename."'<BR>";
        $table_content = $conn->query("SELECT * FROM ".$tablename.";")->fetch_all(MYSQLI_ASSOC);
        //var_dump($table_content);
        //echo count($table_content)." Records Found.<BR>";
        $cache->set("table_".$tablename,json_encode($table_content),0);
        //$sample_record = $conn->query("SELECT * FROM ".$table["Tables_in_".$settings["db"]["database"]]." LIMIT 1")->fetch_all(MYSQLI_ASSOC);
        //$cache->set("table_sample_".$table["Tables_in_".$settings["db"]["database"]],json_encode($sample_record),0);
        $table_description = $conn->query("DESCRIBE ".$tablename)->fetch_all(MYSQLI_ASSOC);
        $cache->set("table_description_".$tablename,json_encode($table_description),0);
        //echo $tablename." Loaded<BR><HR>";
    }
    $cache->set("tables","loaded",0);
}
//CHATGPT SOLUTION

function updateRecord($table, $values, $conditions) {
    global $cache, $conn;

    // update record in Memcached memory
    $table_content = json_decode($cache->get("table_".$table),true);
    foreach ($table_content as &$record) {
        foreach ($conditions as $key => $value) {
            if ($record[$key] == $value) {
                foreach ($values as $field => $new_value) {
                    $record[$field] = $new_value;
                }
            }
        }
    }
    $cache->replace("table_".$table, json_encode($table_content));

    // add query in the cache
    $set_values = "";
    $i = 0;
    foreach ($values as $field => $new_value) {
        if ($i > 0) {
            $set_values .= ", ";
        }
        $set_values .= "$field='$new_value'";
        $i++;
    }
    $where_conditions = "";
    $i = 0;
    foreach ($conditions as $key => $value) {
        if ($i > 0) {
            $where_conditions .= " AND ";
        }
        $where_conditions .= "$key='$value'";
        $i++;
    }

    $queryCache=$cache->get("SQLQ");
    if (!$queryCache) {
        $queryCache=[];
    }
    $queryCache[] = "UPDATE $table SET $set_values WHERE $where_conditions";
    $cache->set("SQLQ",$queryCache,0);
    executeCachedQueries();
}

function deleteRecord($table, $conditions) {
    global $cache, $conn;

    // delete record from Memcached memory
    $table_content = json_decode($cache->get("table_".$table),true);
    foreach ($table_content as $index => $record) {
        foreach ($conditions as $key => $value) {
            if ($record[$key] == $value) {
                unset($table_content[$index]);
            }
        }
    }
    $cache->replace("table_".$table, json_encode($table_content));

    // add query in the cache
    $where_conditions = "";
    $i = 0;
    foreach ($conditions as $key => $value) {
        if ($i > 0) {
            $where_conditions .= " AND ";
        }
        $where_conditions .= "$key='$value'";
        $i++;
    }

    $queryCache=$cache->get("SQLQ");
    if (!$queryCache) {
        $queryCache=[];
    }
    $queryCache[] = "DELETE FROM $table WHERE $where_conditions";
    $cache->set("SQLQ",$queryCache,0);
    executeCachedQueries();
}

function insertRecord($table, $values) {
    global $cache, $conn;

    // Use the DESCRIBE statement to get information about the columns in the table
    $columns = json_decode($cache->get("table_description_".$table),true);

    // Create an array to store the names of the columns with default values or auto-incrementing values
    $default_columns = array();
    $auto_increment_columns = array();

    // Iterate over the columns and add the name of each column with a default value or that is auto-incrementing to the $default_columns array
    foreach ($columns as $column) {
        if ($column["Default"] != null) {
            if ($column["Default"]=="current_timestamp()") {
                $default_columns[$column["Field"]] = date("Y-m-d H:i:s", time());
            }
            else {
                $default_columns[$column["Field"]] = $column["Default"];
            }
            
        }
        if ($column["Extra"] == "auto_increment") {
            $auto_increment_columns[] = $column["Field"];
        }

        $sample_record[] = $column["Field"];
    }

    // Remove the keys for the columns with default values or that are auto-incrementing from the $sample_record array
    foreach (array_keys($default_columns) as $column) {
        if (in_array($column,array_keys($values))) {
            continue;
        }
        unset($sample_record[$column]);
        //unset($auto_increment_columns[$column]);
    }

    // Check if the keys in $values are a subset of the keys in the sample record
    $invalid_keys = array_diff(array_keys($values), ($sample_record));
    if (!empty($invalid_keys)) {
        // throw an error or return without inserting the record
        throw new Exception("Invalid keys in values: " . implode(", ", $invalid_keys). ". Requires at least ". implode(", ", array_keys($sample_record)));
        return;
    }


    $table_content = json_decode($cache->get("table_".$table),true);
            
    foreach ($auto_increment_columns as $column) {
        //$max_value = $conn->query("SELECT MAX($column) FROM $table")->fetch_assoc()[$column];
        $max_value = getMax($table_content,$column);

        $values[$column] = $max_value + 1;
    }

    foreach ($default_columns as $column=>$val) {
        if (in_array($column,array_keys($values))) {
            continue;
        }
        $values[$column] = $val;
    }

    

    // Insert record into Memcached memory
    
    $table_content[] = $values;
    $cache->replace("table_".$table, json_encode($table_content));

    // Removing default records
    // foreach ($default_columns as $column=>$val) {
    //     unset($values[$column]);
    // }

    // add query in the cache
    $fields = "";
    $i = 0;
    foreach ($values as $field => $new_value) {
        if ($i > 0) {
            $fields .= ", ";
        }
        $fields .= "$field";
        $i++;
    }
    $inputValues = "";
    $i = 0;
    foreach ($values as $field => $new_value) {
        if ($i > 0) {
            $inputValues .= ", ";
        }
        $inputValues .= "'$new_value'";
        $i++;
    }

    $queryCache=$cache->get("SQLQ");
    if (!$queryCache) {
        $queryCache=[];
    }
    $queryCache[] = "INSERT INTO $table ($fields) VALUES ($inputValues)";
    $cache->set("SQLQ",$queryCache,0);
    executeCachedQueries();
    
}

function getRecords($table, $conditions = [], $fields = []) {
    global $cache;

    $table_content = json_decode($cache->get("table_".$table),true);
    $records = [];
    foreach ($table_content as &$record) {
        $match = true;
        foreach ($conditions as $key => $value) {
            if ($record[$key] != $value) {
                $match = false;
                break;
            }
        }
        if ($match || empty($conditions)) {
            if (empty($fields)) {
                $records[] = $record;
            } else {
                $selected_fields = array_intersect_key($record, array_flip($fields));
                $records[] = $selected_fields;
            }
        }
    }

    return $records;
}

function executeCachedQueries() {
    global $cache, $conn;
    if (!$cache->get("queryCounter")) {
        $queryCache=$cache->get("SQLQ");
        if ($queryCache) {
            foreach ($queryCache as $query) {
                $conn->query($query);
            }
        }
        $cache->replace("SQLQ",[]);
        $cache->set("queryCounter","1",10);
    }
}


// get all records
//$records = getRecords('users');

// get all records where the name is John
//$records = getRecords('users', ['name' => 'John']);

// get all records, only include name and email fields
//$records = getRecords('users', [], ['name', 'email']);

// get all records where the name is John, only include name and email fields
//$records = getRecords('users', ['name' => 'John'], ['name', 'email']);



// example usage
//updateRecord("users", array("name" => "Jane", "email" => "jane@gmail.com"), array("id" => 1));

//insertRecord('luckydraw',["cid"=>"11512005551","ticket"=>"TEST","event_id"=>2]);
//deleteRecord('luckydraw',["cid"=>"11512005551"]);