<?php
	include_once "utils/api_bhutanapp.php";
  //require_once "utils/visitorlog.php";
  //URL: domain.com/signup/[VENUEID]/?cid=[CITIZEN]
  //$args[0] = "1?cid=11512005551"; //SELF DEFINED FOR TEMPORARY REQUESTS
  try {
    $received_args = explode("?cid=",$args[1]);
    $eventid=(int)$args[0];
    foreach($args as $arg) {
      if (strstr($arg,"?cid=")) {
        $cid = explode("?cid=",$arg)[1];
      }  
    }
  }
  catch (Exception $e) {
    header("Location: /error-403");
    exit();
  }

  
  //client_detail($cid);
  //URL: domain.com/check/[VENUEID]/[CITIZENID]?cid=[SCANNERID]
  
  //if(get("users","id","cid='$cid'")!="[]") { // MEANS ADMIN
  if (False) {
    print_r($args);
    $eventid = $args[0];
    $admincid = explode("?cid=",$args[sizeof($args)-1])[1];
    $cid = explode("?cid=",$args[sizeof($args)-1])[0];
    //echo "Location: /check/2/$cid?cid=$admincid";
    header("Location: /check/2/$cid?cid=$admincid");
    exit();
  }
  $user_detail = json_decode(api_get_phone_detail($cid))->data;
  $eventdetail = json_decode(get("events","name,address,start_datetime,end_datetime,country,capacity","id=$eventid AND end_datetime>NOW()"));
  //var_dump($eventdetail);
  $capacity = (int)$eventdetail[0][5];
  $total_registered = (int)json_decode(get("registration_requests","COUNT(id)","event_id=$eventid"))[0][0];
  $accessingfrom=get_country();
  $registration_detail=json_decode(get("registration_requests","id,withdrawn,other_cids,event_id,register_datetime","cid='".$cid."' AND event_id='$eventid'"));
  if ($total_registered>=$capacity) {
    $generated_form = '<form id="msform">
    <fieldset>
    <img src="'.$settings["app"]["homebase"].'/images/unexpected.png" height="200px" alt="Not supposed to Happen">
    <br>
    <h1 class="fs-title">Sorry! But the registration is full for this event.</h1>
    <h2 class="fs-subtitle"></h2>
    </fieldset>
    </form>';
  }
  else if ($eventdetail[0][4]!=$accessingfrom && false) {
    $generated_form = '<form id="msform">
    <fieldset>
    <img src="'.$settings["app"]["homebase"].'/images/unexpected.png" height="200px" alt="Not supposed to Happen">
    <br>
    <h1 class="fs-title">Sorry! You are not allowed to apply from '.$accessingfrom.'</h1>
    <h2 class="fs-subtitle">This event is only meant for '.$eventdetail[0][4].'</h2>
    </fieldset>
    </form>';
  }
  else if (empty($eventdetail) || count($eventdetail[0])==0) { //No venue or Venue registration time expired
    $temp = json_decode(get("venues","address,location,end","id=$eventid"));
    if (empty($temp)) {
      $generated_form = '<form id="msform">
      <fieldset>
      <img src="'.$settings["app"]["homebase"].'/images/unexpected.png" height="200px" alt="Not supposed to Happen">
      <br>
      <h1 class="fs-title">This was Unexpected!</h1>
      <h2 class="fs-subtitle">We are fixing it as soon as we can!</h2>
      </fieldset>
      </form>';
    }
    else {
      $generated_form = '<form id="msform">
      <fieldset>
      <img src="'.$settings["app"]["homebase"].'/images/too_late.png" height="200px" alt="A bit too late">
      <br>
      <h1 class="fs-title">We are sorry</h1>
      <h2 class="fs-subtitle">The regsitration for <b>'.$temp[0][0].' '.$temp[0][1].'</b> closed on '.$temp[0][2].'</h2>
      </fieldset>
      </form>';
    }
  }
  else if (empty($registration_detail) || count($registration_detail[0])==0) { //No registration found at all so all good to go
    $generated_form = '<form id="msform">
    <h2>Registration for <b>'.$eventdetail[0][0].' '.$eventdetail[0][1].'</b></h2>
    <br>
    <!-- progressbar -->
    <ul id="progressbar">
      <li class="active">Personal Details</li>
      <li>dependent Details</li>
      <li>Confirm and Submit</li>
      <li>Validate</li>
    </ul>
    <!-- fieldsets -->
    <fieldset>
      <h2 class="fs-title">Please Enter your Details</h2>
      <h3 class="fs-subtitle">Hi '.$user_detail->first_name.' '.$user_detail->last_name.'! We would like to know more about you.</h3>
      <input type="hidden" name="eventid" value="'.$eventid.'">
      <label>Citizenship ID</label>
      <input type="cid" name="cid" value="'.$cid.'" disabled />
      <label>Phone (<i>If you have a change in your number, please update it in your profile settings in the app.</i>)</label>
      <input type="phone" name="phone" value="'.$user_detail->phone.'" disabled />
      <label>Date Of Birth</label>
      <input type="date" name="dob" value="'.$user_detail->dob.'" disabled />
      <div class="buttons">
          <input type="button" name="next" class="next action-button" value="Next" />
      </div>
    </fieldset>
    <fieldset>
      <h2 class="fs-title">dependent Details</h2>
      <h3 class="fs-subtitle">Are you bringing any dependent without CID (Minor) or without BhutanApp (Elders)? If so, add by clicking the button below.</h3>

      <div id="dependent_list">
      </div>
      <input type="button" class = "action-button dependentdetail" value="Add +" />
      <div class="buttons">
          <input type="button" name="previous" class="previous action-button" value="Previous" />
          <input type="button" name="next" class="next action-button" value="Next" />
      </div>
    </fieldset>
    <fieldset>
      <h2 class="fs-title">Do you want to submit your registration?</h2>
      <h3 class="fs-subtitle">Please note that you will not be allowed to change the information once submitted. Please check once and reconfirm the details.</h3>
      <div class="buttons">
          <input type="button" name="previous" class="previous action-button" value="Previous" />
          <input type="button" name="submit" class="action-button" id="check_before_submit" value="Submit" />
          <input type="hidden" class="next send_otp" id="proceed_further">
      </div>
    </fieldset>
    <fieldset>
    <h2 class="fs-title">Please enter the OTP you received on your phone below</h2>
    <h3 class="fs-subtitle"></h3>
    <input type="number" name="otp" id="otpvalue" min="1" max="999999">
    <button type="button" style="width:100%" class="send_otp" id="otpbutton" disabled=true>Resend OTP</button>
    <div class="buttons">
        <input type="button" name="verify" class="verify action-button" id="otpverify" value="Verify" />
    </div>
  </fieldset>
  </form>';
    
  }
  else {
    $generated_form = '<form id="msform">
  <h2>Thank you for registering to the event!</h2>
  <h4>Venue: '.$eventdetail[0][0].' '.$eventdetail[0][1].'</h4>
  <h4>From: '.explode(" ",$eventdetail[0][2])[0].' Time '.explode(" ",$eventdetail[0][2])[1].'</h4>
  <h4>Till: '.explode(" ",$eventdetail[0][3])[0].' Time '.explode(" ",$eventdetail[0][3])[1].'</h4>
  '.(($registration_detail[0][1]=="No")?'':'<h4>With dependent: <span id="dependent_list"></span></h4>').'
  <div id="qrcode">
    </div>
</form>';

    $generatescript = 'var qrcode = new QRCode("qrcode", {
      title: "ENTRY CODE",
      titleFont: "bold 20px Arial",
      titleColor: "#000000",
      titleBackgroundColor: "#ffffff",
      titleHeight: 100,
      titleTop: 30, 
    
      subTitle: "Validated by Admins",
      subTitleFont: "12px Arial",
      subTitleColor: "#4F4F4F",
      subTitleTop: 50,
    
      text: "'.$cid.'",
      width: 300,
      height:300,
    
      quietZone: 35,
      quietZoneColor: "#fff",
      autoColor: false, // Automatic color adjustment(for data block)
      autoColorDark: "rgba(0, 0, 0, .6)", // Automatic color: dark CSS color
      autoColorLight: "rgba(255, 255, 255, .7)", // Automatic color: light CSS color
      });
          
      ';
  }
?>
<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
	<link rel="stylesheet" type="text/css" href="slide navbar style.css">
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/select2.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/select2-bootstrap.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/tingle.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/register.css'?>" rel="stylesheet">
<link rel="shortcut icon" href="<?php echo $settings["app"]["homebase"].'/'.$settings["app"]["logo"]?>" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>

<body> 	
    <!-- multistep form -->
    <?php echo $generated_form;?>
<div class="tingle-demo tingle-demo-force-close " style="visibility:hidden">

</div>
</body>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/select2.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/tingle.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/easy.qrcode.min.js'?>"></script>
<script>

// window.navigator.getUserMedia(audio: true, video: true);  
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

var dependent_list=[];
  
    
  function parse_dependent() {
    if (<?php echo isset($generatescript)?"false":"true";?>) {
    table = '<table class="dependent" style="width:90%">';
    for (i=0; i<dependent_list.length; i++) {
      table+='<tr><td>'+(i+1)+'</td><td>'+dependent_list[i][0]+' '+(dependent_list[i][1]==""?'':dependent_list[i][1]+' ')+dependent_list[i][2]+'</td><td>Date of Birth: '+dependent_list[i][3]+'</td>';
      table+='<td><button type="button" onclick="remove_dependent('+i+')" class="closebutton">X</button></td>';
      table+='</tr>';  
      }
      table+='</table>';
    }
    else {
      table = '';
      for (i=0; i<dependent_list.length; i++) {
        table+=' '+dependent_list[i][0]+' '+(dependent_list[i][1]==""?'':dependent_list[i][1]+' ')+dependent_list[i][2]+',';
      }
      table=table.substring(0,table.length-1);
    }
    $("#dependent_list").html(table);
  }

      
  function remove_dependent(index) {
    var confirmation = new tingle.modal({
          closeMethods: ['overlay','escape'],
          footer: true
      });
      confirmation.setContent(`  <div class="" style="padding: 0px; box-shadow: none">
        <h2>Are you sure?</h2>
      </div>`);
      confirmation.addFooterBtn("Yes","action-button tingle-btn--pull-right",function(){
        dependent_list.splice(index,1);
        parse_dependent();
        confirmation.close();
      });

      confirmation.addFooterBtn("No","action-button tingle-btn--pull-right",function(){
        confirmation.close();
      });

      confirmation.open();
    
  }

 


      <?php
      if (!empty($registration_detail)) {
        $set_of_dependent = str_replace("+",",",$registration_detail[0][2]);
        $dependent_detail = json_decode(get("citizen","cid,first_name,middle_name,last_name,dob","FIND_IN_SET(cid,'".$set_of_dependent."')>0"));
        array_merge($dependent_detail,json_decode(get("minor","cid,first_name,middle_name,last_name,dob","FIND_IN_SET(cid,'".$set_of_dependent."')>0")));
        $i=0;
        foreach ($dependent_detail as $dependent) {
          
          echo "dependent_list[$i]=(['".$dependent[1]."','".$dependent[2]."','".$dependent[3]."','".$dependent[4]."','".$dependent[0]."']);";
          $i++;
        }
      }
    
    ?>

parse_dependent();


var seconds=0;
function send_otp() {
  $("#otpvalue").val("");
  $("#otpverify").val("Please wait 5s");
  $("#otpverify").prop("disabled",true);
  setTimeout(function(){$("#otpverify").prop("disabled",false); $("#otpverify").val("Verify")},5000);
  seconds=60;
  $("#otpbutton").prop("disabled",true);
  $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",{"request":"otp","cid":"<?php echo $cid;?>"},function(data){
      console.log("");
      // d=JSON.parse(data);
      // if (d.error===false) {
      //   console.log("success");
      // }
      // else {
      //   console.log(d.error);
      // }
  });
  var x = setInterval(function() {
    seconds--;
    $("#otpbutton").html("Request another OTP in "+seconds+"s");
    if (seconds==0) {
      clearInterval(x);
      $("#otpbutton").prop("disabled",false);
      $("#otpbutton").html("Request another OTP");
    }
  },1000);
}

$("#otpverify").click(function() {


  enteredotp = $("#otpvalue").val();
  if (enteredotp.length!=6) {
    alertify("Otp should be 6 digits. Please enter the correct value");
    return false;
  }

  const array = $("#msform").serializeArray(); // Encodes the set of form elements as an array of names and values.
  const json = {"dependent": JSON.stringify(dependent_list)};
  $.each(array, function () {
    json[this.name] = this.value || "";
  });

  $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",{"data":json, "request":"validate","otp":enteredotp,"cid":"<?php echo $cid;?>"},function(data){
      d=JSON.parse(data);
      if (d.error!==false) {
        $("#otpverify").val(d.error+" Wait 10s");
        $("#otpverify").prop("disabled",true);
        setTimeout(function(){$("#otpverify").prop("disabled",false); $("#otpverify").val("Verify")},10000);
      }
      else {
        location.reload();
      }
  })
});



$("#check_before_submit").click(function(){
  if ($("select[name='job']").val()=="") {
    alertify("You have not completed the form properly. Please check and try again.");
  }
  else {
    $("#proceed_further").click();
  }
});




    //jQuery time
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches


    
    $(".next").click(function(){
      if(animating) return false;
      animating = true;
      
      current_fs = $(this).parent().parent();
      next_fs = $(this).parent().parent().next();
      
      //activate next step on progressbar using the index of next_fs
      $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
      
      //show the next fieldset
      next_fs.show(); 
      //hide the current fieldset with style
      current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
          //as the opacity of current_fs reduces to 0 - stored in "now"
          //1. scale current_fs down to 80%
          scale = 1 - (1 - now) * 0.2;
          //2. bring next_fs from the right(50%)
          left = (now * 50)+"%";
          //3. increase opacity of next_fs to 1 as it moves in
          opacity = 1 - now;
          current_fs.css({
            'transform': 'scale('+scale+')',
            'position': 'absolute'
          });
          next_fs.css({'left': left, 'opacity': opacity});
        }, 
        duration: 800, 
        complete: function(){
          current_fs.hide();
          animating = false;
        }, 
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
      });
    });
    
    $(".previous").click(function(){
      if(animating) return false;
      animating = true;
      
      current_fs = $(this).parent().parent();
      previous_fs = $(this).parent().parent().prev();
      
      //de-activate current step on progressbar
      $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
      
      //show the previous fieldset
      previous_fs.show(); 
      //hide the current fieldset with style
      current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
          //as the opacity of current_fs reduces to 0 - stored in "now"
          //1. scale previous_fs from 80% to 100%
          scale = 0.8 + (1 - now) * 0.2;
          //2. take current_fs to the right(50%) - from 0%
          left = ((1-now) * 50)+"%";
          //3. increase opacity of previous_fs to 1 as it moves in
          opacity = 1 - now;
          current_fs.css({'left': left});
          previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
        }, 
        duration: 800, 
        complete: function(){
          current_fs.hide();
          animating = false;
        }, 
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
      });
    });
    
        $(".submit").click(function(){
          return false;
        })
    
        $(".occupations").select2();
    
    
        
    
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
          dependent_list.push([f,m,l,d,c]);
          parse_dependent();
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
    
$(".send_otp").click(function() {
  if (seconds>0) {
    return false;
  }
  send_otp();
});

$("#otpvalue").keyup(function(){
  if ($(this).val().length > 6) {
    $(this).val($(this).val().slice(0,6)); 
  }
});

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




<?php echo isset($generatescript)?$generatescript:"";?>

  </script>
</html>
<?php
?>