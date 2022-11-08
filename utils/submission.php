<?php
include_once "utils/api_bhutanapp.php";
http_response_code(200);
if (isset($_POST["usermanagement"])) { //VALIDATED
    $data = json_decode($_POST["data"]);
    if (isset($data->userid) && $data->userid=='') {
        if (isset($data->password) && $data->password!='') {
            if (get("admin_user","email","email='$data->email'")=="[]") {
                echo insert("admin_user","email,cid,name,level,password",$data->email.",".$data->cid.",".$data->name.",".$data->level.",".md5($data->password));
            }
            else {
                echo '{"error":"The username/email already exists"}';
            }
        }
        else {
            echo '{"error":"The Password cannot be empty"}';
        }
        
    }
    else if (isset($data->userid) && $data->userid!='') {
        if ($data->password=='') {
            echo update("admin_user","email,cid,name,level",$data->email.",".$data->cid.",".$data->name.",".$data->level,"id='$data->userid'");
        }
        else {
            echo update("admin_user","email,cid,name,level,password",$data->email.",".$data->cid.",".$data->name.",".$data->level.",".md5($data->password),"id='$data->userid'");
        }

    }
    else {
        echo '{"error":"Something Went Wrong. Please contact the Admin"}';
    }

    
}
else if (isset($_POST["eventmanagement"])) { 
    $data = json_decode($_POST["data"]);
    if ($data->eventid=='') {
        if (get("events","id","name='".$data->name."'")=="[]") {
            echo insert("events","name,country,address,image_id,start_datetime,end_datetime,capacity",$data->name.",".$data->country.",".$data->address.",".$data->image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity);
        }
        else {
            echo '{"error":"This Event Name has already been added."}';
        }
    }
    else {
        echo update("events","name,country,address,image_id,start,end,capacity",$data->name.",".$data->country.",".$data->address.",".$data->image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity,"id='$data->id'");
    }

    
}
else {
    http_response_code(405);
    echo '{"error":"Invalid Request"}';
}


?>
