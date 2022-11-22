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
    $cid = base_convert((string)$ticket,10,36)-(int)$eventid;
    //$ticket = strtoupper(base_convert((string)((int)$eventdetail[0][6]+(int)$cid),10,36))
    
// }
if(get("admin_user","admin_id","cid='$admincid'")=="[]") { // CHECK PERMISSION OF WHO IS ACCESSING THE PAGE
    http_response_code(405);
    $data_form = '
    <form id="msform">
  <h2>You are not authorized to use this feature.</h2>
  <img src="'.$settings["app"]["homebase"].'/images/unexpected.png'.'" height=200>
  <h4></h4>
  <h4></h4>
    </form>';
}
else if ($cid && strlen($cid)==11) {
    $registration_detail=json_decode(get("registration_requests","id,withdrawn,other_cids,event_id,register_datetime,is_allowed","cid='".$cid."' AND event_id='$eventid'"));
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
            $base64photo = json_decode(get("images","bin","id='".getphoto($cid)."'"))[0][0];
            $data_form = '
            <form id="msform">
            <h2>REGISTRATION FOR CID: '.$cid.' NOT FOUND</h2>
            <img src="data:image/png;base64, '.$base64photo.'" style="padding:20px; height: 20vh"/>
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

        $eventdetail = json_decode(get("events","id,address,start_datetime,end_datetime","end_datetime>NOW()"));
        $set_of_dependents = trim(str_replace(";",",",$registration_detail[0][2]),",");
        $dependent_detail = json_decode(get("citizens","cid,first_name,middle_name,last_name,dob","FIND_IN_SET(cid,'".$set_of_dependents."')>0"));
        $dependent_detail = array_merge($dependent_detail,json_decode(get("minor","cid,first_name,middle_name,last_name,dob","FIND_IN_SET(cid,'".$set_of_dependents."')>0")));
        $person_detail = json_decode(get("citizens","dob,first_name,middle_name,last_name,phonenumber,image_id","cid='$cid'"));
        $base64photo = json_decode(get("images","bin","id='".$person_detail[0][5]."'"))[0][0];



        $event_options = '';
        foreach($eventdetail as $event) {
            if ($event[0]==$registration_detail[0][3]) {
                $event_options.='<option selected value="'.$event[0].'">'.$event[1].' - '.$event[2].'</option>';
            }
            else {
                $event_options.='<option value="'.$event[0].'">'.$event[1].' - '.$event[2].'</option>';
            }
            
        }

        $dependent_list = '';
        foreach($dependent_detail as $dependent) {
            $dependent_list.='<li class="dependent_list_items"><span>'.$dependent[1]." ".($dependent[2]==""?"":$dependent[2]).' '.$dependent[3]."</span><span> DOB: ".$dependent[4].'</span><button type="button" onclick="discard_dependent(\''.$dependent[0].'\')" class="closebutton">X</button></li>';
        }

        $play_sound="reject";
        if ($registration_detail[0][5]=="0") {
            $entry_status = '<h4 id="statusbar" class="regpending">Entry Status: PENDING</h4>';
        }
        else if ($registration_detail[0][5]=="1") {
            $entry_status = '<h4 id="statusbar"  class="regallowed">Entry Status: ALLOWED</h4>';
            $play_sound="accept";
        }
        else {
            $entry_status = '<h4 id="statusbar" class="regnotallowed">Entry Status: NOT ALLOWED</h4>';
        }
        
        $event_display =json_decode(get("events","name,address","id=".$registration_detail[0][3]))[0];
        $data_form = '
        <form id="msform">
        <fieldset>
        <h2>User Details: '.$event_display[0].' At '.$event_display[1].'</h2>
        '.$entry_status.'
        <hr>
        <label class="fs-title" style="text-align:center; padding:10px">User Registered on '.$registration_detail[0][4].'</label>
        <!--select name="registrations_venueid" id="event_change">
        '.$event_options.'
        </select-->
        <table border=0 style="width:100%">
        <tr><td>
        <img src="data:image/png;base64, '.$base64photo.'" style="padding:20px; height: 20vh"/>
        

        </td><td>
        <label>First Name</label>
        <input type="text" name="citizen_firstname" value="'.$person_detail[0][1].'" disabled>
        <label>Middle Name</label>
        <input type="text" name="citizen_middlename" value="'.$person_detail[0][2].'" disabled>
        <label>Last Name</label>
        <input type="text" name="citizen_lastname" value="'.$person_detail[0][3].'" disabled>
        

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
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
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
          <input type="date" id="dependent_dob" placeholder="Date of Birth" max="2022-08-01"/>
        </fieldset>`);
        modalButtonOnly.addFooterBtn('Add', 'tingle-btn tingle-btn--primary tingle-btn--pull-right', function () {
          c=$("#dependent_cid").val();
          f=$('#dependent_firstname').val();
          m=$('#dependent_middlename').val();
          l=$('#dependent_lastname').val();
          d=$('#dependent_dob').val();
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
            modalButtonOnly.close();
        });

        modalButtonOnly.addFooterBtn('Cancel', 'tingle-btn tingle-btn--default tingle-btn--pull-right', function () {
          $('#dependent_firstname').val('');
          $('#dependent_middlename').val('');
          $('#dependent_lastname').val('');
          $('#dependent_dob').val('');
            modalButtonOnly.close();
        });
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
    

    var get_cid_info = function(cid) {
  if (cid=="<?php echo $cid?>") {
    alertify("You cannot add your own CID again");
    $('#dependent_firstname').prop("disabled",true);
    $('#dependent_middlename').prop("disabled",true);
    $('#dependent_lastname').prop("disabled",true);
    $('#dependent_dob').prop("disabled",true);
    return 0;
  }
  if (cid.length==11) {
    $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",{"findcid":cid, "request":"cidinfo"},function(data){
      d=JSON.parse(data);
      $('#dependent_firstname').prop("disabled",false);
          $('#dependent_middlename').prop("disabled",false);
          $('#dependent_lastname').prop("disabled",false);
          $('#dependent_dob').prop("disabled",false);
      if (d.error!==false) {
        alertify("Please enter the details manually",d.msg);
        $('#dependent_firstname').val('');
          $('#dependent_middlename').val('');
          $('#dependent_lastname').val('');
          $('#dependent_dob').val('');
      }
      else {
        $('#dependent_firstname').val(d.first_name);
          $('#dependent_middlename').val(d.middle_name);
          $('#dependent_lastname').val(d.last_name);
          $('#dependent_dob').val(d.dob);
          $('#dependent_firstname').prop("disabled",true);
          $('#dependent_middlename').prop("disabled",true);
          $('#dependent_lastname').prop("disabled",true);
          $('#dependent_dob').prop("disabled",true);
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
            $("#statusbar").html("Entry Status: NOW ALLOWED");
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