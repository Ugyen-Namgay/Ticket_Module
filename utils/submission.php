<?php
include_once "utils/api_bhutanapp.php";
http_response_code(200);
if (isset($_POST["request"]) && $_POST["request"]=="cidinfo") {
    $cid=$_POST["findcid"];
    $details = get("citizens","first_name,middle_name,last_name,dob","cid='$cid'");
    if ($details=="[]") {
        $user_detail = json_decode(api_get_phone_detail($cid))->data;
        if ($user_detail && isset($user_detail->first_name)) {
            $imageid=getphoto($cid);         
            insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag","$cid,$user_detail->dob,$user_detail->first_name,$user_detail->middle_name,$user_detail->last_name,$user_detail->phone,$imageid,$user_detail->dzongkhag");
            echo '{"error":false,"first_name":"'.$user_detail->first_name.'","middle_name":"'.$user_detail->middle_name.'","last_name":"'.$user_detail->last_name.'","dob":"'.$user_detail->dob.'"}';
        }
        else {
            echo '{"error":true,"msg":"No details could be found for this CID"}';
        }
        
    }
    else {
        $user_detail = json_decode($details)[0];
        echo '{"error":false,"first_name":"'.$user_detail[0].'","middle_name":"'.$user_detail[1].'","last_name":"'.$user_detail[2].'","dob":"'.$user_detail[3].'"}';
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
        $attempts = (int)json_decode(get("otp","attempts","cid='$cid'"))[0][0];
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
            $user_detail = json_decode(api_get_phone_detail($cid))->data;
            $imageid=getphoto($cid);         
            if (get("citizens","cid","cid='$cid'")=="[]") { //INSERT CITIZEN DATA IF NOT THERE
                insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag","$cid,$user_detail->dob,$user_detail->first_name,$user_detail->middle_name,$user_detail->last_name,$user_detail->phone,$imageid,$user_detail->dzongkhag");
            }
            $dependentid="";
            $dependents = json_decode($data["dependent"]);
            foreach($dependents as $dependent) { //LOOK FOR EACH dependent FOR DATA. USE SAME ENTRY IF EXISTS
                $is_there_dependent = json_decode(get("citizens","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"));
                array_merge($is_there_dependent,$is_there_dependent = json_decode(get("minor","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'")));
                //var_dump($is_there_dependent);
                if (is_array($is_there_dependent) && sizeof($is_there_dependent)>0) {
                    $dependentid.=$is_there_dependent[0][0].";";
                }
                else {
                    if (strlen($dependent[4])=="11") {
                        $dependent_user_detail = json_decode(api_get_phone_detail($cid))->data;
                        $dependent_imageid=getphoto($dependent[4]);
                        insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag",$dependent[4].",$dependent_user_detail->dob,$dependent_user_detail->first_name,$dependent_user_detail->middle_name,$dependent_user_detail->last_name,$dependent_user_detail->phone,$dependent_imageid,$dependent_user_detail->dzongkhag");
                        $dependentid.=json_decode(get("citizens","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"))[0][0].";";
                    }
                    else {
                        insert("minor","first_name,middle_name,last_name,dob,parent_cid,cid","$dependent[0],$dependent[1],$dependent[2],$dependent[3],$cid,$dependent[4]");
                        $dependentid.=json_decode(get("minor","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"))[0][0].";";
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
            insert("images","bin,format","$data->uploadedfiles,png");
            $image_id = json_decode(get("images","id","bin='$data->uploadedfiles'"))[0][0];
            echo insert("events","name,country,address,image_id,start_datetime,end_datetime,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset);
        }
        else {
            echo '{"error":"This Event Name has already been added."}';
        }
    }
    else {
        if ($data->uploadedfiles=="") {
            echo update("events","name,country,address,start,end,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset,"id='$data->id'");
        }
        else {
            insert("images","bin,format","$data->uploadedfiles,png");
            $image_id = json_decode(get("images","id","bin='$data->uploadedfiles'"))[0][0];
            echo update("events","name,country,address,image_id,start,end,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset,"id='$data->id'");
        }
        
    }

    
}
else if (isset($_POST["adminupdate"]) && isset($_POST["admincid"])) {
    if(get("users","id","cid='".$_POST["admincid"]."'")=="[]") {
        http_response_code(405);
        echo '{"error":"Not Permissted to perform actions"}';
    }
    $command = $_POST["command"];
    $value = $_POST["value"];
    $cid = $_POST["identity"]; //CID

    

    if ($command=="removedependent") {
        $dependentid=json_decode(get("registration_requests","other_cids","cid='$cid'"))[0][0];
        if ($dependentid=="".$value) {
            update("registration_requests","other_cids","","cid='$cid'");
            // NOT GOING TO UPDATE has_dependent since has_dependent=2 and one record in dependentid will indicate not bringing the dependent.
        }
        else {
            $dependentid=str_replace($value.";","",str_replace(";".$value,"",$dependentid));
            update("registration_requests","other_cids","$dependentid","cid='$cid'");
        }
    }
    else if ($command=="adddependent") {
        $is_there_dependent = json_decode(get("citizens","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]'"));
        array_merge($is_there_dependent,json_decode(get("minor","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]'")));
        if (sizeof($is_there_dependent)>0) {
            $dependentid=$is_there_dependent[0][0];
        }
        else {
            if (strlen($is_there_dependent[0][0])=="11") {
                $dependent_user_detail = json_decode(api_get_phone_detail($cid))->data;
                        $dependent_imageid=getphoto($is_there_dependent[0][0]);
                        insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag",$is_there_dependent[0][0].",$dependent_user_detail->dob,$dependent_user_detail->first_name,$dependent_user_detail->middle_name,$dependent_user_detail->last_name,$dependent_user_detail->phone,$dependent_imageid,$dependent_user_detail->dzongkhag");
                        $dependentid=json_decode(get("citizens","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]'"))[0][0];
            }
            else {
                insert("minor","first_name,middle_name,last_name,dob,parent_cid","$value[0],$value[1],$value[2],$value[3],$cid");
                $dependentid=json_decode(get("minor","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]'"))[0][0];
            }
             
        }
        
        $prev_dependentid=json_decode(get("registrations","dependentid","cid='$cid'"))[0][0];

        if (strrpos($dependentid,$prev_dependentid)===false) {
            if ($prev_dependentid=="") {
                update("registrations","has_dependent,dependentid","1,$dependentid","cid='$cid'");
            }
            else {
                $no_of_dependent =1+ strlen($prev_dependentid.'+'.$dependentid)-strlen(str_replace("+","",$prev_dependentid.'+'.$dependentid));
                update("registrations","dependentid,has_dependent",$prev_dependentid.'+'.$dependentid.",$no_of_dependent","cid='$cid'");        
            }
        }
        else {
            echo '{"error":"dependent Already Added"}';
        }

        
    }
    else if ($command=="approval") {
        $regid = json_decode(get("registrations","id","cid='$cid'"))[0][0];
        $admin = $_POST["admincid"];
        if ($value=="accept") {        
            insert("adminlog","admin,registrationid,action","'$admin','$regid','Accepted Entry'");
            update("registrations","is_allowed","Yes","id='$regid'");
        }
        else {
            insert("adminlog","admin,registrationid,action","'$admin','$regid','Rejected Entry'");
            update("registrations","is_allowed","Denied","id='$regid'");
        }
        echo '{"error":false}';

    }
    else if ($command=="venuechange") {
        $registration = json_decode(get("registrations","id,venueid","cid='$cid'"));
        $regid = $registration[0][0];
        $venueid = $registration[0][1];
        insert("adminlog","admin,registrationid,action","'$admin','$regid','Change Venue from $venueid to $value'");
        update("registrations","venueid","$value","id='$regid'");
        echo '{"error":false}';

    }

}
else {
    http_response_code(405);
    echo '{"error":"Invalid Request"}';
}


?>
