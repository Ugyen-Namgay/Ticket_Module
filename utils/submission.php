<?php
include_once "utils/api_bhutanapp.php";

function cache_frequent_data($key,$output="",$flush=false) {
    global $cache;
    if ($flush) {
        $cache->delete($key);
        return "";
    }
    $cacheresult = $cache->get($key);
    if ($cacheresult) {
        return $cacheresult;
    }
    else {
        $cache->set($key, $output, 10); 
        return $output;
    }
}
function checkOnline($domain) {
    $cacheonline = cache_frequent_data(crc32("checkOnline".$domain));
    if ($cacheonline=="0") {
        return (bool)$cacheonline;
    }
    $curlInit = curl_init($domain);
    curl_setopt($curlInit,CURLOPT_HEADER,false);
    curl_setopt($curlInit,CURLOPT_TIMEOUT_MS,1500);
    //curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
 
    //get answer
    $response = curl_exec($curlInit);
    //$response = $domain;
 
 
    curl_close($curlInit);
    if ($response && $response!="" && $response!="broken") {
        cache_frequent_data(crc32("checkOnline".$domain),"1");
        return true;
    } 
    cache_frequent_data(crc32("checkOnline".$domain),"0");
    return false;
 
 
    // $fp = fSockOpen(str_replace("http://","",$domain), 80, $errno, $errstr, 2); 
    // return $fp!=false;
}
http_response_code(200);
if (isset($_POST["request"]) && $_POST["request"]=="cidinfo") {
    $cid=$_POST["findcid"];
    $details = get("citizens","*","cid='$cid'",true);
    if ($details=="[]") {
        $user_detail = json_decode(api_get_phone_detail($cid))->data;
        if ($user_detail && isset($user_detail->first_name)) {
            //$imageid=getphoto($cid); //Removed to reduce load
            $imageid = "1";
            clear_cache("citizens","*","cid='$cid'");    
            insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag,gender","$cid,$user_detail->dob,$user_detail->first_name,$user_detail->middle_name,$user_detail->last_name,$user_detail->phone,$imageid,$user_detail->dzongkhag,$user_detail->gender");
            echo '{"error":false,"first_name":"'.$user_detail->first_name.'","middle_name":"'.$user_detail->middle_name.'","last_name":"'.$user_detail->last_name.'","dob":"'.$user_detail->dob.'","gender":"'.$user_detail->gender.'"}';
        }
        else {
            $url = 'http://43.230.208.133/cidapi.php?cid='.$cid.'&token=gG3eFFRuPrVW4KH26aTj&podo';
            $checkurl = 'http://43.230.208.133/cidapi.php?cid=11512005551&token=gG3eFFRuPrVW4KH26aTj&podo';
            //["1990-10-06","Phuntsho","Gayenden","Male","171","Dzongkhag"]
            if (!checkOnline($checkurl)) {
                echo '{"error":true,"msg":"Could not connect to census. Please enter the details manually: No details could be found for this CID"}';
            }
            else {

                $ch=curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                $censusdata=curl_exec($ch);
                if ($censusdata!="" && $censusdata!="broken" && strpos($censusdata,'["--",null' )!== 0) {
                    //error_log($censusdata,0);
                    $censusdata=json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $censusdata));
                    $dob = $censusdata[0];
                    $first_name = $censusdata[1];
                    $middle_last_name = explode(" ",$censusdata[2]);
                    if (count($middle_last_name)>1) {
                        $middle_name = $middle_last_name[0];
                        $last_name = $middle_last_name[1];
                    }
                    else {
                        $middle_name="";
                        $last_name = $censusdata[2];
                    }
                    $gender = ($censusdata[3]=="Male")?"M":"F";
                    $dzongkhag = $censusdata[5];
                    $imageid = "1";
                    insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag,gender","$cid,$dob,$first_name,$middle_name,$last_name,'18000000',$imageid,$dzongkhag,$gender");
                    echo '{"error":false,"first_name":"'.$first_name.'","middle_name":"'.$middle_name.'","last_name":"'.$last_name.'","dob":"'.$dob.'","gender":"'.$gender.'"}';
                }  
                else {
                    echo '{"error":true,"msg":"Cannot find details in Census. Please enter the details manually: No details could be found for this CID"}';
                }

            }
            
        }
        
    }
    else {
        // if (empty(json_decode(get("registration_requests","other_cids","(other_cids LIKE '%$cid%' OR cid='$cid') AND event_id=1"),true))) { // HARD CODED AS EVENT 1
            $user_detail = json_decode($details,true)[0];
            echo '{"error":false,"first_name":"'.$user_detail["first_name"].'","middle_name":"'.$user_detail["middle_name"].'","last_name":"'.$user_detail["last_name"].'","dob":"'.$user_detail["dob"].'","gender":"'.$user_detail["gender"].'"}';
        // }
        // else {
        //     echo '{"error":true,"msg":"Sorry, The dependent is already registered by others.","cleardata":true}';
        // }
        
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
        $attempts = json_decode(get("otp","attempts","cid='$cid'"),true);
        if ($otp=="singleregister") {
            $attempts = 0;
        }
        else {
            $attempts = (int)$attempts[0]["attempts"];
        }
        delete("otp","valid_till<DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        if ($attempts>5 && $otp!="singleregister") {
            http_response_code(405);
            echo '{"error":"Too Many Attempts Made. Try after 5 minutes"}';
        }
        if (get("otp","otp","otp='$otp' AND cid='$cid' AND valid_till>DATE_SUB(NOW(), INTERVAL 1 MINUTE)")=="[]" && $otp!="singleregister") {          
            update("otp","attempts",$attempts+1,"cid='$cid'");
            echo '{"error":"Invalid or Expired OTP"}';
        }
        else {
            
            $data = $_POST["data"];        
            if (get("citizens","*","cid='$cid'",true)=="[]") { //INSERT CITIZEN DATA IF NOT THERE
                $user_detail = json_decode(api_get_phone_detail($cid))->data;
                if ($otp=="singleregister") { //Temporary fix to not have images of the users. Its clogging too much!!!
                    $imageid="1"; 
                }
                else {
                    //$imageid=getphoto($cid); //TEMPORARY FIX
                    $imageid="1";
                }                
                insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag,gender","$cid,$user_detail->dob,$user_detail->first_name,$user_detail->middle_name,$user_detail->last_name,$user_detail->phone,$imageid,$user_detail->dzongkhag,$user_detail->gender");
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
                    if (strlen($dependent[4])==11 && (substr($dependent[4],0,1)=="1" || substr($dependent[4],0,1)=="3")) {
                        clear_cache("citizens","*","cid='".$dependent[4]."'");  
                        //$dependent_user_detail = json_decode(api_get_phone_detail($dependent[4]))->data;
                        //$dependent_imageid=getphoto($dependent[4]); //Removed to reduce load
                        $dependent_imageid="1";
                        insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag,gender",$dependent[4].",$dependent[3],$dependent[0],$dependent[1],$dependent[2],$user_detail->phone,$dependent_imageid,$user_detail->dzongkhag,$dependent[5]");
                        $dependentid.=json_decode(get("citizens","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]'"),true)[0]["cid"].";";
                    }
                    else {
                        insert("minor","first_name,middle_name,last_name,dob,parent_cid,cid,gender","$dependent[0],$dependent[1],$dependent[2],$dependent[3],$cid,$dependent[4],$dependent[5]");
                        $dependentid.=json_decode(get("minor","cid","first_name = '$dependent[0]' AND middle_name = '$dependent[1]' AND last_name='$dependent[2]' AND dob='$dependent[3]' AND cid='$dependent[4]' AND gender='$dependent[5]'"),true)[0]["cid"].";";
                    }
                }
                
            }

            //$dependentid=rtrim($dependentid,";");
            if (isset($_POST["autoallow"])) {
                insert("registration_requests","cid,other_cids,event_id,dzongkhag,gewog,is_allowed","$cid,$dependentid,".$data["eventid"].",".$data["dzongkhag"].",".$data["gewog"].",1");
            }
            else {
                insert("registration_requests","cid,other_cids,event_id,dzongkhag,gewog,is_allowed","$cid,$dependentid,".$data["eventid"].",".$data["dzongkhag"].",".$data["gewog"].",0");
            }
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
            echo update("events","name,country,address,start_datetime,end_datetime,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset,"id=$data->eventid");
        }
        else {
            insert("images","bin,format","$data->uploadedfiles,png");
            $image_id = json_decode(get("images","id","bin='$data->uploadedfiles'",true),true)[0]["id"];
            echo update("events","name,country,address,image_id,start_datetime,end_datetime,capacity,ticket_offset",$data->name.",".$data->country.",".$data->address.",".$image_id.",".$data->startdate." ".$data->starttime.",".$data->enddate." ".$data->endtime.",".$data->capacity.",".$data->ticket_offset,"id=$data->eventid");
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
    clear_cache("TICKET".$cid.$eventid);
    
    $regid = json_decode(get("registration_requests","id","cid='".$cid."' AND event_id='$eventid'"),true)[0]["id"];
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
        $citizen_detail = json_decode(get("citizens","*","cid='$cid'",true),true);
        $dependentid="";
        $is_there_dependent = json_decode(get("citizens","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]' AND cid='$value[4]'"),true);
        $is_there_dependent = array_merge($is_there_dependent,json_decode(get("minor","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]'"),true));
        
        if (is_array($is_there_dependent) && sizeof($is_there_dependent)>0) {
            $dependentid.=$is_there_dependent[0]["cid"].";";
        }
        else {
            if (strlen($value[4])==11  && (substr($value[4],0,1)=="1" || substr($value[4],0,1)=="3")) {
                clear_cache("citizens","*","cid='".$is_there_dependent[0]["cid"]."'"); 
                //$dependent_user_detail = json_decode(api_get_phone_detail($is_there_dependent[0]["cid"]))->data;
                //$dependent_imageid=getphoto($is_there_dependent[0]["cid"]);
                $dependent_imageid="1"; // removed for performance
                insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag,gender",$value[4].",$value[3],$value[0],$value[1],$value[2],".$citizen_detail[0]["phone"].",$dependent_imageid,".$citizen_detail[0]["dzongkhag"].",$value[5]");
                //insert("citizens","cid,dob,first_name,middle_name,last_name,phonenumber,image_id,dzongkhag,gender",$value[4].",$dependent_user_detail->dob,$dependent_user_detail->first_name,$dependent_user_detail->middle_name,$dependent_user_detail->last_name,$dependent_user_detail->phone,$dependent_imageid,$dependent_user_detail->dzongkhag,$dependent_user_detail->gender");
                $dependentid.=json_decode(get("citizens","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]' AND cid='$value[4]'"),true)[0]["cid"].";";
            }
            else {
                insert("minor","first_name,middle_name,last_name,dob,parent_cid,cid,gender","$value[0],$value[1],$value[2],$value[3],$cid,$value[4],$value[5]");
                $dependentid.=json_decode(get("minor","cid","first_name = '$value[0]' AND middle_name = '$value[1]' AND last_name='$value[2]' AND dob='$value[3]' AND cid='$value[4]' AND gender='$value[5]'"),true)[0]["cid"].";";
            }
        }
        $prev_dependentid=json_decode(get("registration_requests","other_cids","id=$regid"),true)[0]["other_cids"];
        $prev_dependentid=empty($prev_dependentid)?"":trim($prev_dependentid,";");
        //echo "Dependent ID: ".$dependentid."; PREVIOUS ID:".$prev_dependentid."; FINDING: ".print_r(strpos($dependentid,$prev_dependentid)).";";
        
        if (strpos($prev_dependentid,$dependentid)===false) {
            update("registration_requests","other_cids",ltrim($prev_dependentid.";".$dependentid),"id=$regid");
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
