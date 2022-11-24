<?php
include_once "utils/api_bhutanapp.php";
http_response_code(200);
if (isset($_POST["request"]) && $_POST["request"]=="cidinfo") {
    $cid=$_POST["findcid"];
    $details = get("citizens","*","cid='$cid'",true);
    if ($details=="[]") {
        $user_detail = json_decode(api_get_phone_detail($cid))->data;
        if ($user_detail && isset($user_detail->first_name)) {
            $imageid=getphoto($cid);
            clear_cache("citizens","*","cid='$cid'");    
            insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag","$cid,$user_detail->dob,$user_detail->first_name,$user_detail->middle_name,$user_detail->last_name,$user_detail->phone,$imageid,$user_detail->dzongkhag");
            echo '{"error":false,"first_name":"'.$user_detail->first_name.'","middle_name":"'.$user_detail->middle_name.'","last_name":"'.$user_detail->last_name.'","dob":"'.$user_detail->dob.'"}';
        }
        else {
            echo '{"error":true,"msg":"Please enter the details manually: No details could be found for this CID"}';
        }
        
    }
    else {
        if (empty(json_decode(get("registration_requests","other_cids","other_cids LIKE '%$cid%' AND NOT cid='$cid'"),true))) {
            $user_detail = json_decode($details,true)[0];
            echo '{"error":false,"first_name":"'.$user_detail["first_name"].'","middle_name":"'.$user_detail["middle_name"].'","last_name":"'.$user_detail["last_name"].'","dob":"'.$user_detail["dob"].'"}';
        }
        else {
            echo '{"error":true,"msg":"Sorry, The dependent is already registered by others.","cleardata":true}';
        }
        
    }
}
else if (isset($_POST["request"]) && isset($_POST["cid"])) {
    $otp=generateOTP();
    $cid=$_POST["cid"];
    if ($_POST["request"]=="otp") {
        if (get("otp","otp","cid='$cid' AND valid_till>DATE_SUB(NOW(), INTERVAL 1 MINUTE)")=="[]") {
            delete("otp","valid_till<DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
            $user_detail = json_decode(api_get_phone_detail($cid))->data;
            $message="Please confirm your registration using OTP ".$otp;
            send_sms($user_detail->phone,$message);
            echo insert("otp","cid,otp,valid_till,attempts","$cid,$otp,NOW(),0");
            
        }
        else {
            echo '{"error":"Active OTP Still exists"}';
        }
    }
    else if ($_POST["request"]=="validate") {
        $otp=$_POST["otp"];
        $attempts = (int)json_decode(get("otp","attempts","cid='$cid'"),true)[0]["attempts"];
        delete("otp","valid_till<DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        if ($attempts>5) {
            http_response_code(405);
            echo '{"error":"Too Many Attempts Made. Try after 5 minutes"}';
        }
        if (get("otp","otp","otp='$otp' AND cid='$cid' AND valid_till>DATE_SUB(NOW(), INTERVAL 1 MINUTE)")=="[]") {          
            update("otp","attempts",$attempts+1,"cid='$cid'");
            echo '{"error":"Invalid or Expired OTP"}';
        }
        else {
            
            $data = $_POST["data"];        
            if (get("citizens","*","cid='$cid'",true)=="[]") { //INSERT CITIZEN DATA IF NOT THERE
                $user_detail = json_decode(api_get_phone_detail($cid))->data;
                $imageid=getphoto($cid); 
                insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag","$cid,$user_detail->dob,$user_detail->first_name,$user_detail->middle_name,$user_detail->last_name,$user_detail->phone,$imageid,$user_detail->dzongkhag");
            }
            $dependentid="";
            $dependents = json_decode($data["dependent"]);
            foreach($dependents as $dependent) { //LOOK FOR EACH dependent FOR DATA. USE SAME ENTRY IF EXISTS
                $is_there_dependent = json_decode(get("citizens","*","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"),true);
                $is_there_dependent = array_merge($is_there_dependent,json_decode(get("minor","*","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"),true));
                //var_dump($is_there_dependent);
                if (is_array($is_there_dependent) && sizeof($is_there_dependent)>0) {
                    $dependentid.=$is_there_dependent[0]["cid"].";";
                }
                else {
                    if (strlen($dependent[4])=="11") {
                        clear_cache("citizens","*","cid='".$dependent[4]."'");  
                        $dependent_user_detail = json_decode(api_get_phone_detail($dependent[4]))->data;
                        $dependent_imageid=getphoto($dependent[4]);
                        insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag",$dependent[4].",$dependent_user_detail->dob,$dependent_user_detail->first_name,$dependent_user_detail->middle_name,$dependent_user_detail->last_name,$dependent_user_detail->phone,$dependent_imageid,$dependent_user_detail->dzongkhag");
                        $dependentid.=json_decode(get("citizens","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"),true)[0]["cid"].";";
                    }
                    else {
                        insert("minor","first_name,middle_name,last_name,dob,parent_cid,cid","$dependent[0],$dependent[1],$dependent[2],$dependent[3],$cid,$dependent[4]");
                        $dependentid.=json_decode(get("minor","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"),true)[0]["cid"].";";
                    }
                }
                
            }

            $dependentid=rtrim($dependentid,";");
            
            insert("registration_requests","cid,other_cids,event_id","$cid,$dependentid,".$data["eventid"]."");
            http_response_code(200);
            echo '{"error":false}';


        }
    }
}
else if (isset($_POST["usermanagement"])) { //VALIDATED
    $data = json_decode($_POST["data"]);
    if (isset($data->admin_id) && $data->admin_id=='') {
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
    else if (isset($data->admin_id) && $data->admin_id!='') {
        if ($data->password=='') {
            echo update("admin_user","email,cid,name,level",$data->email.",".$data->cid.",".$data->name.",".$data->level,"admin_id='$data->admin_id'");
        }
        else {
            echo update("admin_user","email,cid,name,level,password",$data->email.",".$data->cid.",".$data->name.",".$data->level.",".md5($data->password),"admin_id='$data->admin_id'");
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
            insert("images","bin,format","$data->uploadedfiles,png");
            $image_id = json_decode(get("images","id","bin='$data->uploadedfiles'",true),true)[0]["id"];
            echo insert("events","name,country,address,image_id,start_datetime,end_datetime,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset);
        }
        else {
            echo '{"error":"This Event Name has already been added."}';
        }
    }
    else {
        if ($data->uploadedfiles=="") {
            echo update("events","name,country,address,start_datetime,end_datetime,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset,"id='$data->eventid'");
        }
        else {
            insert("images","bin,format","$data->uploadedfiles,png");
            $image_id = json_decode(get("images","id","bin='$data->uploadedfiles'",true),true)[0]["id"];
            echo update("events","name,country,address,image_id,start_datetime,end_datetime,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset,"id='$data->eventid'");
        }
        
    }

    
}
else if (isset($_POST["adminupdate"]) && isset($_POST["admincid"])) {
    if(get("admin_user","admin_id","cid='".$_POST["admincid"]."'")=="[]") {
        http_response_code(405);
        echo '{"error":"Not Permitted to perform actions"}';
        exit();
    }
    $command = $_POST["command"];
    $value = $_POST["value"];
    $cid = $_POST["identity"]; //CID
    $eventid = $_POST["eventid"];

    
    $regid = json_decode(get("registration_requests","id","cid='".$cid."' AND event_id='$eventid'"),true);
    if ($command=="removedependent") {
        
        $dependentid=json_decode(get("registration_requests","*","id=$regid"),true)[0]["other_cids"];
        if ($dependentid=="".$value) {
            echo update("registration_requests","other_cids","","id=$regid");
            // NOT GOING TO UPDATE has_dependent since has_dependent=2 and one record in dependentid will indicate not bringing the dependent.
        }
        else {
            $dependentid=str_replace($value.";","",str_replace(";".$value,"",$dependentid));
            echo update("registration_requests","other_cids","$dependentid","id=$regid");
        }
    }
    else if ($command=="adddependent") {
        $dependentid="";
        $is_there_dependent = json_decode(get("citizens","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]' AND cid='$value[4]'"),true);
        $is_there_dependent = array_merge($is_there_dependent,json_decode(get("minor","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]'"),true));
        
        if (is_array($is_there_dependent) && sizeof($is_there_dependent)>0) {
            $dependentid.=$is_there_dependent[0]["cid"].";";
        }
        else {
            if (strlen($value[4])=="11") {
                clear_cache("citizens","*","cid='".$dependent[4]."'"); 
                $dependent_user_detail = json_decode(api_get_phone_detail($value[4]))->data;
                $dependent_imageid=getphoto($dependent[4]);
                insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag",$value[4].",$dependent_user_detail->dob,$dependent_user_detail->first_name,$dependent_user_detail->middle_name,$dependent_user_detail->last_name,$dependent_user_detail->phone,$dependent_imageid,$dependent_user_detail->dzongkhag");
                $dependentid.=json_decode(get("citizens","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]' AND cid='$value[4]'"),true)[0]["cid"].";";
            }
            else {
                insert("minor","first_name,middle_name,last_name,dob,parent_cid,cid","$value[0],$value[1],$value[2],$value[3],$cid,$value[4]");
                $dependentid.=json_decode(get("minor","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]' AND cid='$value[4]'"),true)[0]["cid"].";";
            }
        }
        $prev_dependentid=json_decode(get("registration_requests","other_cids","id=$regid"),true)[0]["other_cids"];
        $prev_dependentid=empty($prev_dependentid)?"":$prev_dependentid;
        //echo "Dependent ID: ".$dependentid."; PREVIOUS ID:".$prev_dependentid."; FINDING: ".print_r(strpos($dependentid,$prev_dependentid)).";";
        
        if (strpos($prev_dependentid,$dependentid)===false) {
            update("registration_requests","other_cids","$prev_dependentid$dependentid","id=$regid");
            echo '{"error":false}';
        }
        else {
            echo '{"error":"Dependent Already Added"}';
        }

        
    }
    else if ($command=="approval") {
        $regid = json_decode(get("registration_requests","id","cid='$cid'"),true)[0]["id"];
        $eventid = json_decode(get("registration_requests","event_id","cid='$cid'"),true)[0]["event_id"];
        $admin = $_POST["admincid"];
        if ($value=="accept") {        
            insert("logs","admin_id,event_id,action","'$admin','$eventid','Accepted Entry'");
            update("registration_requests","is_allowed","1","id=$regid");
        }
        else {
            insert("logs","admin_id,event_id,action","'$admin','$eventid','Rejected Entry'");
            update("registration_requests","is_allowed","0","id=$regid");
        }
        echo '{"error":false}';

    }
    else if ($command=="venuechange") {
        $registration = json_decode(get("registration_requests","id,event_id","cid='$cid'"),true);
        $regid = $registration[0][0];
        $eventid = $registration[0][1];
        insert("logs","admin,event_id,action","'$admin','$eventid','Change Venue from $eventid to $value'");
        update("registration_requests","event_id","$value","id=$regid");
        echo '{"error":false}';

    }

}
else {
    http_response_code(405);
    echo '{"error":"Invalid Request"}';
}


?>
