<?php
	include_once "utils/api_bhutanapp.php";
  //require_once "utils/visitorlog.php";
  //URL: domain.com/signup/[VENUEID]/?cid=[CITIZEN]
  //$args[0] = "1?cid=11512005551"; //SELF DEFINED FOR TEMPORARY REQUESTS
  try {
    $received_args = explode("?cid=",$args[1]);
    $eventid=(int)$args[0];
    $cid = "";
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

  $is_there_cached_ticket = get_cache("TICKET".$cid.$eventid);
  if ($is_there_cached_ticket) {
    echo $is_there_cached_ticket;
    exit();
  }
  $cache_ticket = false;
  //client_detail($cid);
  //URL: domain.com/check/[VENUEID]/[CITIZENID]?cid=[SCANNERID]
  
  //if(get("users","id","cid='$cid'")!="[]") { // MEANS ADMIN
  if (False) {
    //print_r($args);
    $eventid = $args[0];
    $admincid = explode("?cid=",$args[sizeof($args)-1])[1];
    $cid = explode("?cid=",$args[sizeof($args)-1])[0];
    //echo "Location: /check/2/$cid?cid=$admincid";
    header("Location: /check/2/$cid?cid=$admincid");
    exit();
  }
  $settings = parse_ini_file("settings/config.ini", true);
  $eventdetail = json_decode(get("events","*","id=$eventid",true),true);
  $timeexpired=false;
  if (time()>strtotime($eventdetail[0]["end_datetime"])) { // END OF TIME
    $eventdetail=[];
    $timeexpired=true;
    $capacity = 10000000;
  }
  else {
    $capacity = (int)$eventdetail[0]["capacity"];
  }
  //var_dump($eventdetail);
  //$capacity = (int)$eventdetail[0]["capacity"];
  $total_registered = (int)json_decode(get("registration_requests","COUNT(id) as num","event_id=$eventid"),true)[0]["num"];
  $accessingfrom=get_country();
  $regid = json_decode(get("registration_requests","id","cid='".$cid."' AND event_id='$eventid'"),true);
  if ($total_registered>=$capacity) {
    $generated_form = '<form id="msform">
    <fieldset>
    <img src="'.$settings["app"]["homebase"].'/images/unexpected.png" height="200px" alt="Not supposed to Happen">
    <br>
    <h1 class="fs-title">Sorry! But the capcity of the registration is full for this event.</h1>
    <h2 class="fs-subtitle"></h2>
    </fieldset>
    </form>';
  }
  else if ($timeexpired) {
    $temp = json_decode(get("venues","address,location,end","id=$eventid"),true);
    $generated_form = '<form id="msform">
    <fieldset>
    <img src="'.$settings["app"]["homebase"].'/images/too_late.png" height="200px" alt="A bit too late">
    <br>
    <h1 class="fs-title">We are sorry</h1>
    <h2 class="fs-subtitle">The regsitration for <b>'.$temp[0][0].' '.$temp[0][1].'</b> closed on '.$temp[0][2].'</h2>
    </fieldset>
    </form>'; 
  }

  else if ($eventdetail[0]["country"]!=$accessingfrom && false) {
    $generated_form = '<form id="msform">
    <fieldset>
    <img src="'.$settings["app"]["homebase"].'/images/unexpected.png" height="200px" alt="Not supposed to Happen">
    <br>
    <h1 class="fs-title">Sorry! You are not allowed to apply from '.$accessingfrom.'</h1>
    <h2 class="fs-subtitle">This event is only meant for '.$eventdetail[0]["country"].'</h2>
    </fieldset>
    </form>';
  }
  else if (empty($eventdetail) || count($eventdetail[0])==0) { //No venue or Venue registration time expired
    $temp = json_decode(get("venues","address,location,end","id=$eventid"),true);
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
  else if (empty($regid) || count($regid[0])==0) { //No registration found at all so all good to go
    $user_detail = json_decode(api_get_phone_detail($cid))->data;
    $generated_form = '<form id="msform">
    
    <h2>Registration for <b>'.$eventdetail[0]["name"].' '.$eventdetail[0]["address"].'</b></h2>
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
      <label>Your Current Address</label>
      <div id="currentlocation" style="width:100%; display: inline-flex">
    <select class="form-control form-select" id="dzongkhag" name="dzongkhag" required style="background-color: aliceblue;color: #010101;">
        <option value="" disabled="" selected="">Dzongkhag</option>
        <option value="Bumthang">Bumthang</option>
        <option value="Chhukha">Chhukha</option>
        <option value="Dagana">Dagana</option>
        <option value="Gasa">Gasa</option>
        <option value="Haa">Haa</option>
        <option value="Lhuentse">Lhuentse</option>
        <option value="Mongar">Mongar</option>
        <option value="Paro">Paro</option>
        <option value="Pemagatshel">Pemagatshel</option>
        <option value="PhuentsholingThrom">Phuentsholing Throm</option>
        <option value="Punakha">Punakha</option>
        <option value="Samdrupjongkhar">Samdrupjongkhar</option>
        <option value="Samtse">Samtse</option>
        <option value="Sarpang">Sarpang</option>
        <option value="ThimThrom">Thim Throm</option>
        <option value="Thimphu">Thimphu</option>
        <option value="Trashigang">Trashigang</option>
        <option value="Trashiyangtse">Trashiyangtse</option>
        <option value="Trongsa">Trongsa</option>
        <option value="Tsirang">Tsirang</option>
        <option value="WangduePhodrang">Wangdue Phodrang</option>
        <option value="Zhemgang">Zhemgang</option>
    </select>
    <select class="form-control form-select" id="gewog" name="gewog" required style="background-color: aliceblue;color: #010101;">
        <option value="" disabled="" selected="" style="display: none;">Gewog</option>
        <option value="Chumey" class="Bumthang" style="display: none;">Chumey</option>
        <option value="Chokhor" class="Bumthang" style="display: none;">Chokhor</option>
        <option value="Tang" class="Bumthang" style="display: none;">Tang</option>
        <option value="Ura" class="Bumthang" style="display: none;">Ura</option>
        <option value="Bongo(Sorchen)" class="Chhukha" style="display: none;">Bongo (Sorchen)</option>
        <option value="Dungna" class="Chhukha" style="display: none;">Dungna</option>
        <option value="Darla" class="Chhukha" style="display: none;">Darla</option>
        <option value="Geling" class="Chhukha" style="display: none;">Geling</option>
        <option value="Lokchina" class="Chhukha" style="display: none;">Lokchina</option>
        <option value="Metekha" class="Chhukha" style="display: none;">Metekha</option>
        <option value="Getena" class="Chhukha" style="display: none;">Getena</option>
        <option value="Bjacho" class="Chhukha" style="display: none;">Bjacho</option>
        <option value="Phuentsholing" class="Chhukha" style="display: none;">Phuentsholing</option>
        <option value="Chapcha" class="Chhukha" style="display: none;">Chapcha</option>
        <option value="Sampheling" class="Chhukha" style="display: none;">Sampheling</option>
        <option value="Trashiding" class="Dagana" style="display: none;">Trashiding</option>
        <option value="Karmaling" class="Dagana" style="display: none;">Karmaling</option>
        <option value="Kana" class="Dagana" style="display: none;">Kana</option>
        <option value="Tshangkha" class="Dagana" style="display: none;">Tshangkha</option>
        <option value="Lhamozingkha" class="Dagana" style="display: none;">Lhamozingkha</option>
        <option value="Lajab" class="Dagana" style="display: none;">Lajab</option>
        <option value="Tseza(Kana)" class="Dagana" style="display: none;">Tseza( Kana)</option>
        <option value="Drujeygang" class="Dagana" style="display: none;">Drujeygang</option>
        <option value="Dorona" class="Dagana" style="display: none;">Dorona</option>
        <option value="Nechula" class="Dagana" style="display: none;">Nechula</option>
        <option value="Gesarling" class="Dagana" style="display: none;">Gesarling</option>
        <option value="Khebisa" class="Dagana" style="display: none;">Khebisa</option>
        <option value="Goshi" class="Dagana" style="display: none;">Goshi</option>
        <option value="Tsendagang" class="Dagana" style="display: none;">Tsendagang</option>
        <option value="Khamae" class="Gasa" style="display: none;">Khamae</option>
        <option value="Khatoe" class="Gasa" style="display: none;">Khatoe</option>
        <option value="Laya" class="Gasa" style="display: none;">Laya</option>
        <option value="Lunana" class="Gasa" style="display: none;">Lunana</option>
        <option value="Katsho" class="Haa" style="display: none;">Katsho</option>
        <option value="Eusu" class="Haa" style="display: none;">Eusu</option>
        <option value="Samar" class="Haa" style="display: none;">Samar</option>
        <option value="Sangbay" class="Haa" style="display: none;">Sangbay</option>
        <option value="Gakiling" class="Haa" style="display: none;">Gakiling</option>
        <option value="Bji" class="Haa" style="display: none;">Bji</option>
        <option value="Menbi" class="Lhuentse" style="display: none;">Menbi</option>
        <option value="Menji" class="Lhuentse" style="display: none;">Menji</option>
        <option value="Metsho" class="Lhuentse" style="display: none;">Metsho</option>
        <option value="Khoma" class="Lhuentse" style="display: none;">Khoma</option>
        <option value="Jaray" class="Lhuentse" style="display: none;">Jaray</option>
        <option value="Gangzur" class="Lhuentse" style="display: none;">Gangzur</option>
        <option value="Tshenkhar" class="Lhuentse" style="display: none;">Tshenkhar</option>
        <option value="Kurtoe" class="Lhuentse" style="display: none;">Kurtoe</option>
        <option value="Chali" class="Mongar" style="display: none;">Chali</option>
        <option value="Drametse" class="Mongar" style="display: none;">Drametse</option>
        <option value="Sherimuhung" class="Mongar" style="display: none;">Sherimuhung</option>
        <option value="Saling" class="Mongar" style="display: none;">Saling</option>
        <option value="Tsakaling" class="Mongar" style="display: none;">Tsakaling</option>
        <option value="Mongar" class="Mongar" style="display: none;">Mongar</option>
        <option value="Ngatsang" class="Mongar" style="display: none;">Ngatsang</option>
        <option value="Balam" class="Mongar" style="display: none;">Balam</option>
        <option value="Jurmey" class="Mongar" style="display: none;">Jurmey</option>
        <option value="Narang" class="Mongar" style="display: none;">Narang</option>
        <option value="Silambi" class="Mongar" style="display: none;">Silambi</option>
        <option value="Gongdue" class="Mongar" style="display: none;">Gongdue</option>
        <option value="Drepong" class="Mongar" style="display: none;">Drepong</option>
        <option value="Tsamang" class="Mongar" style="display: none;">Tsamang</option>
        <option value="Khengkhar" class="Mongar" style="display: none;">Khengkhar</option>
        <option value="Thangrong" class="Mongar" style="display: none;">Thangrong</option>
        <option value="Wangchang" class="Paro" style="display: none;">Wangchang</option>
        <option value="Dopshari" class="Paro" style="display: none;">Dopshari</option>
        <option value="Lamgong" class="Paro" style="display: none;">Lamgong</option>
        <option value="Shaba" class="Paro" style="display: none;">Shaba</option>
        <option value="Dogar" class="Paro" style="display: none;">Dogar</option>
        <option value="Hungrel" class="Paro" style="display: none;">Hungrel</option>
        <option value="Throm" class="Paro" style="display: none;">Throm</option>
        <option value="Doteng" class="Paro" style="display: none;">Doteng</option>
        <option value="Lungnyi" class="Paro" style="display: none;">Lungnyi</option>
        <option value="Naja" class="Paro" style="display: none;">Naja</option>
        <option value="Tsento" class="Paro" style="display: none;">Tsento</option>
        <option value="Shumar" class="Pemagatshel" style="display: none;">Shumar</option>
        <option value="Norbugang" class="Pemagatshel" style="display: none;">Norbugang</option>
        <option value="Khar" class="Pemagatshel" style="display: none;">Khar</option>
        <option value="Chongshing" class="Pemagatshel" style="display: none;">Chongshing</option>
        <option value="Dechheling" class="Pemagatshel" style="display: none;">Dechheling</option>
        <option value="Chimung" class="Pemagatshel" style="display: none;">Chimung</option>
        <option value="Choekhorling" class="Pemagatshel" style="display: none;">Choekhorling</option>
        <option value="Yurung" class="Pemagatshel" style="display: none;">Yurung</option>
        <option value="Zobel" class="Pemagatshel" style="display: none;">Zobel</option>
        <option value="Dungmin" class="Pemagatshel" style="display: none;">Dungmin</option>
        <option value="Nanong" class="Pemagatshel" style="display: none;">Nanong</option>
        <option value="PhuentsholingThrom" class="PhuentsholingThrom" style="display: none;">Phuentsholing Throm</option>
        <option value="Barp" class="Punakha" style="display: none;">Barp</option>
        <option value="Toedpisa" class="Punakha" style="display: none;">Toedpisa</option>
        <option value="Chubu" class="Punakha" style="display: none;">Chubu</option>
        <option value="Lingmukha" class="Punakha" style="display: none;">Lingmukha</option>
        <option value="Teowang" class="Punakha" style="display: none;">Teowang</option>
        <option value="Shengana" class="Punakha" style="display: none;">Shengana</option>
        <option value="Goenshari" class="Punakha" style="display: none;">Goenshari</option>
        <option value="Kabjisa" class="Punakha" style="display: none;">Kabjisa</option>
        <option value="Guma" class="Punakha" style="display: none;">Guma</option>
        <option value="Dzomesa" class="Punakha" style="display: none;">Dzomesa</option>
        <option value="Talo" class="Punakha" style="display: none;">Talo</option>
        <option value="SamdrupjongkharThrom" class="Samdrupjongkhar" style="display: none;">Samdrupjongkhar Throm</option>
        <option value="PhuntshoRabtenling" class="Samdrupjongkhar" style="display: none;">Phuntsho Rabtenling</option>
        <option value="Serthi" class="Samdrupjongkhar" style="display: none;">Serthi</option>
        <option value="Orong" class="Samdrupjongkhar" style="display: none;">Orong</option>
        <option value="Wangphu" class="Samdrupjongkhar" style="display: none;">Wangphu</option>
        <option value="Langchenphu" class="Samdrupjongkhar" style="display: none;">Langchenphu</option>
        <option value="Gomdar" class="Samdrupjongkhar" style="display: none;">Gomdar</option>
        <option value="Phuntshothang/Bakuli" class="Samdrupjongkhar" style="display: none;">Phuntshothang/Bakuli</option>
        <option value="Pemathang/Dalim" class="Samdrupjongkhar" style="display: none;">Pemathang/Dalim</option>
        <option value="Samrang" class="Samdrupjongkhar" style="display: none;">Samrang</option>
        <option value="Lauri" class="Samdrupjongkhar" style="display: none;">Lauri</option>
        <option value="Deothang" class="Samdrupjongkhar" style="display: none;">Deothang</option>
        <option value="Martshala" class="Samdrupjongkhar" style="display: none;">Martshala</option>
        <option value="Denchukha" class="Samtse" style="display: none;">Denchukha</option>
        <option value="Tading" class="Samtse" style="display: none;">Tading</option>
        <option value="Pugli" class="Samtse" style="display: none;">Pugli</option>
        <option value="Ghumauney/Yoesheltse" class="Samtse" style="display: none;">Ghumauney/Yoesheltse</option>
        <option value="SamtseThrom" class="Samtse" style="display: none;">Samtse Throm</option>
        <option value="Dorokha" class="Samtse" style="display: none;">Dorokha</option>
        <option value="Dumtoe" class="Samtse" style="display: none;">Dumtoe</option>
        <option value="Sipsu/Tashichholing" class="Samtse" style="display: none;">Sipsu/Tashichholing</option>
        <option value="Gomtu/Phuntshopelri" class="Samtse" style="display: none;">Gomtu/Phuntshopelri</option>
        <option value="Chargharey/Sangacholing" class="Samtse" style="display: none;">Chargharey/Sangacholing</option>
        <option value="Samtse" class="Samtse" style="display: none;">Samtse</option>
        <option value="Nainital/Ugyentse" class="Samtse" style="display: none;">Nainital/Ugyentse</option>
        <option value="Lahireni/Namgaycholing" class="Samtse" style="display: none;">Lahireni/Namgaycholing</option>
        <option value="Biru/Pemaling" class="Samtse" style="display: none;">Biru/Pemaling</option>
        <option value="Bara/Norgaygang" class="Samtse" style="display: none;">Bara/Norgaygang</option>
        <option value="Tendu" class="Samtse" style="display: none;">Tendu</option>
        <option value="Chengmari/Norbugang" class="Samtse" style="display: none;">Chengmari/Norbugang</option>
        <option value="Umling" class="Sarpang" style="display: none;">Umling</option>
        <option value="Dovan" class="Sarpang" style="display: none;">Dovan</option>
        <option value="Shompangkha" class="Sarpang" style="display: none;">Shompangkha</option>
        <option value="Hilley" class="Sarpang" style="display: none;">Hilley</option>
        <option value="Tarathang" class="Sarpang" style="display: none;">Tarathang</option>
        <option value="Jigmechoeling" class="Sarpang" style="display: none;">Jigmechoeling</option>
        <option value="Dekiling" class="Sarpang" style="display: none;">Dekiling</option>
        <option value="Sershong" class="Sarpang" style="display: none;">Sershong</option>
        <option value="Bhur" class="Sarpang" style="display: none;">Bhur</option>
        <option value="Singay" class="Sarpang" style="display: none;">Singay</option>
        <option value="Chuzagang" class="Sarpang" style="display: none;">Chuzagang</option>
        <option value="Gelephu" class="Sarpang" style="display: none;">Gelephu</option>
        <option value="GelephuThrom" class="Sarpang" style="display: none;">Gelephu Throm</option>
        <option value="ThimThrom" class="ThimThrom" style="display: none;">Thim Throm</option>
        <option value="Dagala" class="Thimphu" style="display: none;">Dagala</option>
        <option value="Genekha" class="Thimphu" style="display: none;">Genekha</option>
        <option value="Chang" class="Thimphu" style="display: none;">Chang</option>
        <option value="Kawang" class="Thimphu" style="display: none;">Kawang</option>
        <option value="Lingzhi" class="Thimphu" style="display: none;">Lingzhi</option>
        <option value="Mewang" class="Thimphu" style="display: none;">Mewang</option>
        <option value="Naro" class="Thimphu" style="display: none;">Naro</option>
        <option value="Soe" class="Thimphu" style="display: none;">Soe</option>
        <option value="Shongphu" class="Trashigang" style="display: none;">Shongphu</option>
        <option value="Bartsham" class="Trashigang" style="display: none;">Bartsham</option>
        <option value="Yangnyer" class="Trashigang" style="display: none;">Yangnyer</option>
        <option value="Samkhar" class="Trashigang" style="display: none;">Samkhar</option>
        <option value="Radhi" class="Trashigang" style="display: none;">Radhi</option>
        <option value="Khaling" class="Trashigang" style="display: none;">Khaling</option>
        <option value="Uzarung" class="Trashigang" style="display: none;">Uzarung</option>
        <option value="Bidung" class="Trashigang" style="display: none;">Bidung</option>
        <option value="Phongme" class="Trashigang" style="display: none;">Phongme</option>
        <option value="Sakteng" class="Trashigang" style="display: none;">Sakteng</option>
        <option value="Kangpara" class="Trashigang" style="display: none;">Kangpara</option>
        <option value="Thrimshing" class="Trashigang" style="display: none;">Thrimshing</option>
        <option value="Merak" class="Trashigang" style="display: none;">Merak</option>
        <option value="Kanglung" class="Trashigang" style="display: none;">Kanglung</option>
        <option value="Lumang" class="Trashigang" style="display: none;">Lumang</option>
        <option value="Yallang" class="Trashiyangtse" style="display: none;">Yallang</option>
        <option value="Teotsho" class="Trashiyangtse" style="display: none;">Teotsho</option>
        <option value="Yangtse" class="Trashiyangtse" style="display: none;">Yangtse</option>
        <option value="(Tongshang)" class="Trashiyangtse" style="display: none;">(Tongshang)</option>
        <option value="Jamkhar" class="Trashiyangtse" style="display: none;">Jamkhar</option>
        <option value="Ramjar" class="Trashiyangtse" style="display: none;">Ramjar</option>
        <option value="Khamdang" class="Trashiyangtse" style="display: none;">Khamdang</option>
        <option value="Bumdelling" class="Trashiyangtse" style="display: none;">Bumdelling</option>
        <option value="Langthil" class="Trongsa" style="display: none;">Langthil</option>
        <option value="Korphu" class="Trongsa" style="display: none;">Korphu</option>
        <option value="Tangsibji" class="Trongsa" style="display: none;">Tangsibji</option>
        <option value="Drakten" class="Trongsa" style="display: none;">Drakten</option>
        <option value="Nubee" class="Trongsa" style="display: none;">Nubee</option>
        <option value="Kikhorthang" class="Tsirang" style="display: none;">Kikhorthang</option>
        <option value="Patala" class="Tsirang" style="display: none;">Patala</option>
        <option value="Dunglagang" class="Tsirang" style="display: none;">Dunglagang</option>
        <option value="Gosarling" class="Tsirang" style="display: none;">Gosarling</option>
        <option value="Mendrelgang" class="Tsirang" style="display: none;">Mendrelgang</option>
        <option value="Damphu" class="Tsirang" style="display: none;">Damphu</option>
        <option value="Phuentenchu" class="Tsirang" style="display: none;">Phuentenchu</option>
        <option value="Tsholingkhar" class="Tsirang" style="display: none;">Tsholingkhar</option>
        <option value="Beteni/Patsaling" class="Tsirang" style="display: none;">Beteni/Patsaling</option>
        <option value="Barshong" class="Tsirang" style="display: none;">Barshong</option>
        <option value="Rangthangling" class="Tsirang" style="display: none;">Rangthangling</option>
        <option value="Tsirangtoe" class="Tsirang" style="display: none;">Tsirangtoe</option>
        <option value="Semzong" class="Tsirang" style="display: none;">Semzong</option>
        <option value="Phangyul" class="WangduePhodrang" style="display: none;">Phangyul</option>
        <option value="Nahi" class="WangduePhodrang" style="display: none;">Nahi</option>
        <option value="Sephu" class="WangduePhodrang" style="display: none;">Sephu</option>
        <option value="Kazhi" class="WangduePhodrang" style="display: none;">Kazhi</option>
        <option value="Athang" class="WangduePhodrang" style="display: none;">Athang</option>
        <option value="Dangchu" class="WangduePhodrang" style="display: none;">Dangchu</option>
        <option value="Gangtey" class="WangduePhodrang" style="display: none;">Gangtey</option>
        <option value="Thedtsho" class="WangduePhodrang" style="display: none;">Thedtsho</option>
        <option value="Rubesa" class="WangduePhodrang" style="display: none;">Rubesa</option>
        <option value="GaseTshogom" class="WangduePhodrang" style="display: none;">Gase Tshogom</option>
        <option value="Nyisho" class="WangduePhodrang" style="display: none;">Nyisho</option>
        <option value="Phobji" class="WangduePhodrang" style="display: none;">Phobji</option>
        <option value="Bjena" class="WangduePhodrang" style="display: none;">Bjena</option>
        <option value="Daga" class="WangduePhodrang" style="display: none;">Daga</option>
        <option value="Bjoka" class="Zhemgang" style="display: none;">Bjoka</option>
        <option value="Shingkhar" class="Zhemgang" style="display: none;">Shingkhar</option>
        <option value="Trong" class="Zhemgang" style="display: none;">Trong</option>
        <option value="Nangla" class="Zhemgang" style="display: none;">Nangla</option>
        <option value="Nangkor" class="Zhemgang" style="display: none;">Nangkor</option>
        <option value="Goshing" class="Zhemgang" style="display: none;">Goshing</option>
        <option value="Bardo" class="Zhemgang" style="display: none;">Bardo</option>
        <option value="Phangkhar" class="Zhemgang" style="display: none;">Phangkhar</option>
    </select>
</div>

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

  $generatescript ='
  options = $("#gewog option");
  $("#dzongkhag").change(function(){
    $("#gewog").html(options);
    $("#gewog option").each(function(k,e){
      if (e.className==$("#dzongkhag").val()) {
        $(e).prop("style","display:block;");
        $(e).show();
      }
      else {
        $(e).prop("style","display:none;");
        $(e).hide();
        $(e).remove();
      }
    });

  });
  
  
  ';
    
  }
  else {
    $registration_detail=json_decode(get("registration_requests","*","id=".$regid[0]['id'],true),true);
    $cache_ticket = true;
    $generated_form = '<form id="msform">
    <h1>'.strtoupper($eventdetail[0]["name"]).'</h1>
    <h3>2022</h3>
    <br>
  <h3 style="font-family: Arial"> ENTRY CODE</h3>
  <div id="qrcode">
    </div>
    
  <div style="font-family: Arial">
  <h2>Ticket Number: '.strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$cid),10,36)).'</h2>

  <br>
  <hr>
  <h4>Venue: '.$eventdetail[0]["address"].'</h4>
  <h4>From: '.explode(" ",$eventdetail[0]["start_datetime"])[0].' Time '.explode(" ",$eventdetail[0]["start_datetime"])[1].'</h4>
  <h4>Till: '.explode(" ",$eventdetail[0]["end_datetime"])[0].' Time '.explode(" ",$eventdetail[0]["end_datetime"])[1].'</h4>
  '.(($registration_detail[0]["other_cids"]=="")?'':'<h4>Together With:<br> <i><span id="dependent_list"></span></i></h4>').'
    <br>
  </div>
</form>';

    //print_r($registration_detail);

    $generatescript = 'var qrcode = new QRCode("qrcode", {
      // title: "ENTRY CODE",
      // titleFont: "bold 20px Arial",
      // titleColor: "#000000",
      // titleBackgroundColor: "#ffffff",
      // titleHeight: 100,
      // titleTop: 30, 
    
      subTitle: "",
      subTitleFont: "12px Arial",
      subTitleColor: "#4F4F4F",
      subTitleTop: 50,
      
      text: "'.strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$cid),10,36)).'",
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
<?php
ob_end_clean();
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
	<!-- <link rel="stylesheet" type="text/css" href="slide/navbar/style.css"> -->
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
<script src="<?php echo $settings["app"]["homebase"].'/js/jquery.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/jquery.easing.min.js'?>"></script>
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
      table+='<tr><td>'+(i+1)+'</td><td>'+dependent_list[i][0]+' '+(dependent_list[i][1]==""?'':dependent_list[i][1]+' ')+dependent_list[i][2]+'</td><td>Date of Birth: '+dependent_list[i][3]+'</td><td>Gender: '+dependent_list[i][4]+'</td>';
      table+='<td><button type="button" onclick="remove_dependent('+i+')" class="closebutton">X</button></td>';
      table+='</tr>';  
      }
      table+='</table>';
    }
    else {
      table = '';
      //strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$cid),10,36))
      var offset = <?php echo $eventdetail[0]["ticket_offset"]?>;
      for (i=0; i<dependent_list.length; i++) {
        table+=' '+dependent_list[i][0]+' '+(dependent_list[i][1]==""?'':dependent_list[i][1]+' ')+dependent_list[i][2]+' (TICKET: '+(offset+parseInt(dependent_list[i][4])).toString(36).toUpperCase()+' ) <br>';
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
        $set_of_dependent = trim($registration_detail[0]["other_cids"],";");
        $dependent_detail=[];
        foreach (explode(";",$set_of_dependent) as $dcid) {
          $dependent_detail = array_merge($dependent_detail,json_decode(get("citizens","*","cid='$dcid'",true),true));
          $dependent_detail = array_merge($dependent_detail,json_decode(get("minor","*","cid='$dcid'",true),true));
        }   
        $i=0;
        foreach ($dependent_detail as $dependent) {
          echo "dependent_list[$i]=(['".$dependent["first_name"]."','".$dependent["middle_name"]."','".$dependent["last_name"]."','".$dependent["dob"]."','".$dependent["cid"]."','".$dependent["gender"]."']);";
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
  if ($("select[name='gewog']").val()=="" || $("select[name='dzongkhag']").val()=="" || $("select[name='gewog']").val()==null || $("select[name='dzongkhag']").val()==null) {
    alertify("You have not entered your current address properly. Please check and try again.");
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
    
    var toggleminor = function() {
        if ($("#minortoggle").prop("checked")==true) {
          r = Math.floor(Math.random()*1000000)+60000000000+parseInt('<?php echo $cid?>');
          $("#dependent_cid").val(r);

          //r = (Math.random() + 1).toString(36).substring(7);
          //$("#dependent_cid").val("minor_"+r+"_"+"<?php echo $cid?>");
          $("#dependent_cid").prop("type","hidden");
          $('#dependent_firstname').prop("disabled",false);
          $('#dependent_middlename').prop("disabled",false);
          $('#dependent_lastname').prop("disabled",false);
          $('#dependent_dob').prop("disabled",false);
          $('#dependent_gender').prop("disabled",false);
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
        modalButtonOnly.setContent(`<fieldset class="modal-field" style="padding: 0px; box-shadow: none">
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
          <input type="text" id="dependent_dob" placeholder="Date of Birth"/>
        </fieldset>`);
        $("#dependent_dob").on("focusout",function(){$("#dependent_dob").attr("type","text")});
        $("#dependent_dob").on("focus",function(){$("#dependent_dob").attr("type","date")});
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
          dependent_list.push([f,m,l,d,c,g]);
          parse_dependent();
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
        $('#dependent_cid').val(cid);
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




<?php echo isset($generatescript)?$generatescript:"";?>

  </script>
</html>
<?php
$html = ob_get_contents();
if ($cache_ticket) {
  set_cache("TICKET".$cid.$eventid,$html,0);
}
?>