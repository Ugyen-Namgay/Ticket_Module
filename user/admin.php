<?php
include_once "utils/api_bhutanapp.php";
//URL: domain.com/check/[VENUEID]/[CITIZENID]?cid=[SCANNERID]
$play_sound="reject";//USED LATER.
$eventid = $args[0];
// if (strpos($args[1],"?cid=")===false) {
//     $cid=$args[1];
//     $admincid = "";
// }
// else {
    $admincid = explode("?cid=",$args[sizeof($args)-1])[1];
    $ticket = explode("?cid=",$args[sizeof($args)-1])[0];
    $offset = json_decode(get("events","*","id=$eventid",true),true)[0]["ticket_offset"];
    $cid = (string)((int)base_convert($ticket,36,10)-(int)$offset);
    //$ticket = strtoupper(base_convert((string)((int)$eventdetail[0][6]+(int)$cid),10,36))
    
// }

$regid_get = json_decode(get("registration_requests","id","cid='".$cid."' AND event_id='$eventid'"),true);
$admin_id = json_decode(get("admin_user","admin_id","cid='$admincid'",true),true);

if(empty($admin_id)) { // CHECK PERMISSION OF WHO IS ACCESSING THE PAGE
    http_response_code(405);
    $data_form = '
    <form id="msform">
  <h2>You are not authorized to use this feature.</h2>
  <img src="'.$settings["app"]["homebase"].'/images/unexpected.png'.'" height=200>
  <h4></h4>
  <h4></h4>
    </form>';
}
else if (empty($regid_get)) {
    $data_form = '
    <form id="msform">

    <h2>USER WITH CID: '.$cid.' NOT FOUND IN THE EVENT</h2>
    <br>
    <i>Registration ID not found for this CID for the event</i>
    
    </form>';

}
else if ($cid && strlen($cid)==11) {
    $regid = $regid_get[0]["id"]; 
    $registration_detail=json_decode(get("registration_requests","*","id=$regid",true),true);
    if(sizeof($registration_detail)==0) {
        $user_detail = json_decode(api_get_phone_detail($cid))->data;
        $error = json_decode(api_get_phone_detail($cid))->error;
        $message = json_decode(api_get_phone_detail($cid))->message;
        if ($error) {
            $data_form = '
            <form id="msform">

            <h2>USER WITH CID: '.$cid.' NOT FOUND IN THE APP</h2>
            
            </form>';
        }
        else {
            
            $base64photo = json_decode(get("images","bin","id='".getphoto($cid)."'",true),true)[0]["bin"];
            $data_form = '
            <form id="msform">
            <h2>REGISTRATION FOR CID: '.$cid.' NOT FOUND</h2>
            <img src="data:image/png;base64,'.$base64photo.'" style="padding:20px; height: 20vh"/>
            <hr>
            <h4>First Name: '.$user_detail->first_name.'</h4>
            <h4>Middle Name: '.$user_detail->middle_name.'</h4>
            <h4>Last Name: '.$user_detail->last_name.'</h4>
            <h4>Date of Birth: '.$user_detail->dob.'</h4>
            <h4>Phone: '.$user_detail->phone.'</h4>
            
            </form>';
        }


    }
    else {

        $eventdetail = json_decode(get("events","id,name,address,start_datetime,end_datetime","end_datetime>NOW()"),true);
        $set_of_dependent = trim($registration_detail[0]["other_cids"],";");
        $dependent_detail=[];
        foreach (explode(";",$set_of_dependent) as $dcid) {
          $dependent_detail = array_merge($dependent_detail,json_decode(get("citizens","*","cid='$dcid'",true),true));
          $dependent_detail = array_merge($dependent_detail,json_decode(get("minor","*","cid='$dcid'",true),true));
        //   $dependent_detail[] = json_decode(get("citizens","*","cid='$dcid'",true),true);
        //   $dependent_detail[] = json_decode(get("minor","*","cid='$dcid'",true),true);        
        }
        $person_detail = json_decode(get("citizens","*","cid='$cid'",true),true);
        $base64photo = json_decode(get("images","bin","id='".$person_detail[0]["image_id"]."'",true),true)[0]["bin"];


        $event_options = '';
        foreach($eventdetail as $event) {
            if ($event["id"]==$registration_detail[0]["event_id"]) {
                $event_options.='<option selected value="'.$event["id"].'">'.$event["name"].' - '.$event["address"].'</option>';
            }
            else {
                $event_options.='<option value="'.$event["id"].'">'.$event["name"].' - '.$event["address"].'</option>';
            }
            
        }

        $dependent_list = '';
        foreach($dependent_detail as $dependent) {
            $dependent_list.='<li class="dependent_list_items"><span>'.$dependent["first_name"]." ".($dependent["middle_name"]==""?"":$dependent["middle_name"]).' '.$dependent["last_name"]."</span><span> DOB: ".$dependent["dob"]."</span><span> Gender: ".$dependent["gender"].'<span><button type="button" onclick="discard_dependent(\''.$dependent["cid"].'\')" class="closebutton">X</button></li>';
        }

        $play_sound="reject";
        
        // if ($registration_detail[0]["is_allowed"]=="0") {
        //     $entry_status = '<h4 id="statusbar" class="regpending">Entry Status: PENDING</h4>';
        // }
        // else 
        if ($registration_detail[0]["is_allowed"]=="1") {
            $entry_status = '<h4 id="statusbar"  class="regallowed">Entry Status: ALLOWED</h4>';
            $play_sound="accept";
        }
        else {
            $entry_status = '<h4 id="statusbar" class="regnotallowed">Entry Status: NOT ALLOWED</h4>';
        }
        $event_display =json_decode(get("events","*","id=".$registration_detail[0]["event_id"],true),true)[0];
        $data_form = '
        <form id="msform">
        <fieldset>
        <h2>User Details: '.$event_display["name"].' At '.$event_display["address"].'</h2>
        '.$entry_status.'
        <hr>
        <label class="fs-title" style="text-align:center; padding:10px">User Registered on '.$registration_detail[0]["register_datetime"].'</label>
        <!--select name="registrations_venueid" id="event_change">
        '.$event_options.'
        </select-->
        <table border=0 style="width:100%">
        <tr><td>
        <img src="data:image/png;base64, '.$base64photo.'" style="padding:20px; height: 20vh"/>
        
        
        </td><td>
        <label>First Name</label>
        <input type="text" name="citizen_firstname" value="'.$person_detail[0]["first_name"].'" disabled>
        <label>Middle Name</label>
        <input type="text" name="citizen_middlename" value="'.$person_detail[0]["middle_name"].'" disabled>
        <label>Last Name</label>
        <input type="text" name="citizen_lastname" value="'.$person_detail[0]["last_name"].'" disabled>
        

        </td></tr>
        </table>
        


        <h3 style="text-align:center; padding:10px">Dependents</h3>
        <ol id="dependentlist" style="width:90%">
            '.$dependent_list.'
        </ol>
        <button type="button" class="dependentdetail action-button" style="background: burlywood; width: 80%;">Add Dependent</button>
        <hr/>

        <div class="buttons" style="margin-top: 20px; position:relative; top:0px">
        <button type="button" class="action-button" id="reject">Disallow</button>
        <button type="button" class="action-button" id="accept" style="background-color: #1eb683">Allow</button>
        </div>
        </fieldset>
        </form>
        ';
    }
}
else {
    $data_form = '
    <form id="msform">
  <h2>Admin Panel</h2>
  <h4>Please Scan the QR Code of the user from top corner button</h4>
  <h4></h4>
  <h4></h4>
    </form>';
}




?>
<!DOCTYPE html>
<html>
<head>
	<title>Administrators</title>
	<!-- <link rel="stylesheet" type="text/css" href="slide navbar style.css"> -->
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/select2.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/select2-bootstrap.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/tingle.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/register.css'?>" rel="stylesheet">
<link rel="shortcut icon" href="<?php echo $settings["app"]["homebase"].'/'.$settings["app"]["logo"]?>" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<body> 	
    <?php echo $data_form;?>
</body>
<script src="<?php echo $settings["app"]["homebase"].'/js/jquery.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/jquery.easing.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/select2.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/tingle.min.js'?>"></script>
<script>
    function alertify(message) {
        var alert = new tingle.modal({
            closeMethods: [],
            footer: true
        });
        alert.setContent(`<label>`+message+`</label>`);
        alert.addFooterBtn('OK', 'tingle-btn tingle-btn--primary tingle-btn--pull-right', function () {
            alert.close();
        });
        alert.open();
    }
    
    var toggleminor = function() {
        if ($("#minortoggle").prop("checked")==true) {
          r = (Math.random() + 1).toString(36).substring(7);
          $("#dependent_cid").val("minor_"+r+"_"+"<?php echo $cid?>");
          $("#dependent_cid").prop("type","hidden");
          $('#dependent_firstname').prop("disabled",false);
          $('#dependent_middlename').prop("disabled",false);
          $('#dependent_lastname').prop("disabled",false);
          $('#dependent_dob').prop("disabled",false);
        }
        else {
          $("#dependent_cid").val("");
          $("#dependent_cid").prop("type","text");
        }
    }

    var btn5 = document.querySelector('.dependentdetail');
        if (btn5) {
          var modalButtonOnly = new tingle.modal({
            closeMethods: [],
            footer: true,
            stickyFooter: true
        });
    btn5.addEventListener('click', function () {
            modalButtonOnly.open();
        });
        //modalButtonOnly.setContent(document.querySelector('.tingle-demo-force-close').innerHTML);
        modalButtonOnly.setContent(`  <fieldset class="modal-field" style="padding: 0px; box-shadow: none">
          <h2>Please put your dependent information here</h2>
          <br><div id="dependent_error" style="position: fixed; top: 15px; color: crimson;"></div><br><hr><br>
          <div class="form-check form-switch" style="display:flex;justify-content: flex-start;width: 100%;">
              <input class="form-check-input" type="checkbox" style="float:left;width:60px; height:20px; " id="minortoggle" onchange="toggleminor()">
              <label class="form-check-label" style="padding-left:20px;" for="autoformat"><strong style="font-size:14px"><span id="autoformattext">Minor &nbsp&nbsp</span></strong></label>
          </div>
          <input type="text" id="dependent_cid" placeholder="CID" onchange="get_cid_info(this.value)" />
          <input type="text" id="dependent_firstname" placeholder="First Name" />
          <input type="text" id="dependent_middlename" placeholder="Middle Name" />
          <input type="text" id="dependent_lastname" placeholder="Last Name" />
          <select id="dependent_gender" required>
            <option value="" disabled>Gender</option>
            <option value="M">Male</option>
            <option value="F">Female</option>
          </select>
          <input type="date" id="dependent_dob" placeholder="Date of Birth" max="2022-08-01"/>
        </fieldset>`);
        modalButtonOnly.addFooterBtn('Add', 'tingle-btn tingle-btn--primary tingle-btn--pull-right', function () {
          c=$("#dependent_cid").val();
          f=$('#dependent_firstname').val();
          m=$('#dependent_middlename').val();
          l=$('#dependent_lastname').val();
          d=$('#dependent_dob').val();
          g=$('#dependent_gender').val();
          if (f=='' || d=='') {
            
            $("#dependent_error").html("First name and Date of Birth is mandatory");
            $("#dependent_error").show(100);
            (f=='')?$('#dependent_firstname').focus():$('#dependent_dob').focus();
            setTimeout(()=>{$("#dependent_error").slideUp(500)},2000);
            return false;
          }
          data = {
            "adminupdate":"adminupdate",
            "eventid":"<?php echo $eventid;?>",
            "admincid":"<?php echo $admincid;?>",
            "command": "adddependent",
            "value" : [f,m,l,d,c],
            "identity": "<?php echo $cid?>"
            }
            $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",data,function(data){
                d=JSON.parse(data);
                if (d.error) {
                    alertify(d.error);
                }
                else {
                    location.reload();
                }
            });
            
          $("#dependent_cid").val('');
          $('#dependent_firstname').val('');
          $('#dependent_middlename').val('');
          $('#dependent_lastname').val('');
          $('#dependent_dob').val('');
          $('#dependent_gender').val('');
          $("#minortoggle").prop("checked",false);
          toggleminor();
            modalButtonOnly.close();
        });

        modalButtonOnly.addFooterBtn('Cancel', 'tingle-btn tingle-btn--default tingle-btn--pull-right', function () {
          $('#dependent_firstname').val('');
          $('#dependent_middlename').val('');
          $('#dependent_lastname').val('');
          $('#dependent_dob').val('');
          $('#dependent_gender').val('');
          $("#minortoggle").prop("checked",false);
          toggleminor();
            modalButtonOnly.close();
        });
        }
 
    
    
var get_cid_info = function(cid) {
  toggleminor();
  if (cid=="<?php echo $cid?>") {
    alertify("You cannot add your own CID again");
    $('#dependent_firstname').prop("disabled",true);
    $('#dependent_middlename').prop("disabled",true);
    $('#dependent_lastname').prop("disabled",true);
    $('#dependent_dob').prop("disabled",true);
    $('#dependent_gender').prop("disabled",true);
    return 0;
  }
  if (cid.length==11) {
    $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",{"findcid":cid, "request":"cidinfo"},function(data){
      d=JSON.parse(data);
      $('#dependent_firstname').prop("disabled",false);
      $('#dependent_middlename').prop("disabled",false);
      $('#dependent_lastname').prop("disabled",false);
      $('#dependent_dob').prop("disabled",false);
      $('#dependent_gender').prop("disabled",false);
      if (d.error!==false) {
        alertify(d.msg);
        if (d.cleardata) {
          $('#dependent_cid').val('');
        }
        $('#dependent_gender').val('');
        $('#dependent_firstname').val('');
        $('#dependent_middlename').val('');
        $('#dependent_lastname').val('');
        $('#dependent_dob').val('');
      }
      else {
        $('#dependent_firstname').val(d.first_name);
        $('#dependent_cid').val(cid);
        $('#dependent_gender').val(d.gender);
        $('#dependent_middlename').val(d.middle_name);
        $('#dependent_lastname').val(d.last_name);
        $('#dependent_dob').val(d.dob);
        $('#dependent_firstname').prop("disabled",true);
        $('#dependent_middlename').prop("disabled",true);
        $('#dependent_lastname').prop("disabled",true);
        $('#dependent_dob').prop("disabled",true);
        $('#dependent_gender').prop("disabled",true);
      }
    });
  }
  
}

    var accepttune = document.createElement('audio');
    var rejecttune = document.createElement('audio');
    accepttune.setAttribute('src','<?php echo $settings["app"]["homebase"].'/resources/accept.wav'?>');
    rejecttune.setAttribute('src','<?php echo $settings["app"]["homebase"].'/resources/reject.wav'?>');

    function reloadstatus(a) {
        $("#statusbar").removeClass("regpending");
        $("#statusbar").removeClass("regallowed");
        $("#statusbar").removeClass("regnotallowed");
        if (a=="regallowed") {
            $("#statusbar").html("Entry Status: ALLOWED");
        }    
        else {
            $("#statusbar").html("Entry Status: NOT ALLOWED");
        }
        $("#statusbar").addClass(a);
    }

    $("#accept").click(function(){
        data = {
            "adminupdate":"adminupdate",
            "eventid":"<?php echo $eventid;?>",
            "admincid":"<?php echo $admincid;?>",
            "command": "approval",
            "value" : "accept",
            "identity": "<?php echo $cid?>"
            }
        $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",data,function(data){
                d = JSON.parse(data);
                if (d.error) {
                    rejecttune.play();
                    alertify(d.error);
                }
                else {
                    accepttune.pause();
                    accepttune.currentTime = 0;
                    accepttune.play();
                    reloadstatus("regallowed");
                    //location.reload();
                }                   
        });
        
    });
    $("#reject").click(function(){
        data = {
            "adminupdate":"adminupdate",
            "eventid":"<?php echo $eventid;?>",
            "admincid":"<?php echo $admincid;?>",
            "command": "approval",
            "value" : "reject",
            "identity": "<?php echo $cid?>"
            }
        $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",data,function(data){
                d = JSON.parse(data);
                if (d.error) {
                    rejecttune.play();
                    alertify(d.error);
                }
                else {
                    rejecttune.pause();
                    rejecttune.currentTime = 0;
                    rejecttune.play();
                    reloadstatus("regnotallowed");
                    //location.reload();
                }                   
        });
        
    });

    $("#event_change").change(function(){
        data = {
            "adminupdate":"adminupdate",
            "eventid":"<?php echo $eventid;?>",
            "admincid":"<?php echo $admincid;?>",
            "command": "venuechange",
            "value" : $(this).val(),
            "identity": "<?php echo $cid?>"
            }
        $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",data,function(data){
                d = JSON.parse(data);
                if (d.error) {
                    rejecttune.play();
                    alertify(d.error);
                }
                else {
                    //rejecttune.play();
                    console.log(data);
                    location.reload();
                }                   
        });
        
    });

    

    function discard_dependent(id) {
        data = {
            "adminupdate":"adminupdate",
            "eventid":"<?php echo $eventid;?>",
            "admincid":"<?php echo $admincid;?>",
            "command": "removedependent",
            "value" : id,
            "identity": "<?php echo $cid?>"
        }
        $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",data,function(data){
            d=JSON.parse(data);
                if (d.error) {
                    alertify(d.error);
                }
                else {
                    location.reload();
                }
        });
    }

    </script>
    <!-- <embed src='<?php echo $settings["app"]["homebase"].'/resources/'.$play_sound.'.wav'?>' hidden=true autostart=true loop=false> -->
    </html>