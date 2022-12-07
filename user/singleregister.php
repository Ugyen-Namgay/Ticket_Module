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
        break;
      }  
    }
  }
  catch (Exception $e) {
    header("Location: /error-403");
    exit();
  }

  $cache_ticket = false;
  //clear_cache("TICKET".$cid.$eventid);
  $is_there_cached_ticket = get_cache("TICKET".$cid.$eventid);
  if ($is_there_cached_ticket) {
    echo $is_there_cached_ticket;
    exit();
  }
  
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
  if (time()>strtotime($eventdetail[0]["end_datetime"])) { // END OF TIME
    $eventdetail=[];
  }
  //var_dump($eventdetail);
  $capacity = (int)$eventdetail[0]["capacity"];
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
    <select class="form-control form-select" id="dzongkhag" name="dzongkhag" required>
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
    <select class="form-control form-select" id="gewog" name="gewog" required>
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
          <input type="button" name="next" class="action-button" id="check_before_submit" value="Register" />
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
    $date=date_create($eventdetail[0]["end_datetime"]);
    $cache_ticket = true;
    $ticket = strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$cid),10,36));
    $generated_form = '<form id="msform">
    <!--h1>'.strtoupper($eventdetail[0]["name"]).'</h1>
    <h3>2022</h3-->
    <br>
    <link href="'.$settings["app"]["homebase"].'/css/raffleticket.css" rel="stylesheet">
    <div class="ticket" style="max-width: 100vw;">
    <div class="left">
      <div class="ticket-info">
        <p class="date">
          <span>'.date_format($date,"F").'</span>
          <span>'.date_format($date,"Y").'</span>
          <span class="june-29">'.date_format($date,"dS").'</span>
        </p>
        <div class="show-name">
          <h4 style="color: #000">TICKET</h4>
          <h2 style="text-shadow: 0 1px 0px black;">#'.$ticket.'</h2>
        </div>
        <div class="time">
          <!--p>8:00 PM <span>TO</span> 11:00 PM</p-->
        </div>
        <p class="location"><span>'.($eventdetail[0]["address"]).'</span>
          <span class="separator"></span><span>'.strtoupper($eventdetail[0]["country"]).'</span>
        </p>
      </div>
    </div>
    <div class="right">
      <p class="admit-one">
        <span>115</span>
        <span>NATIONAL</span>
        <span>DAY</span>
      </p>
      <div class="right-info-container">
        <div class="show-name">
          <h1>'.($eventdetail[0]["name"]).'</h1>
        </div>
        <!--div class="time">
          <p>8:00 PM <span>TO</span> 11:00 PM</p>
          <p>DOORS <span>@</span> 7:00 PM</p>
        </div-->
        <div class="qrcode" id="qrcode">
        </div>
        <p class="ticket-number">
          #'.$ticket.'
        </p>
      </div>
    </div>
  </div>
  <h3 style="font-family: Arial"></h3>
  <div id="qrcode.ifneeded">
    </div>
  
  <div style="font-family: Arial">
  <!--h2>Ticket Number: '.$ticket.'</h2-->

  <br>
  <hr>
  <!--h4>Venue: '.$eventdetail[0]["address"].'</h4>
  <h4>From: '.explode(" ",$eventdetail[0]["start_datetime"])[0].' Time '.explode(" ",$eventdetail[0]["start_datetime"])[1].'</h4>
  <h4>Till: '.explode(" ",$eventdetail[0]["end_datetime"])[0].' Time '.explode(" ",$eventdetail[0]["end_datetime"])[1].'</h4>
  '.(($registration_detail[0]["other_cids"]=="")?'':'<h4>Together With:<br> <i><span id="dependent_list"></span></i></h4>').'
    <br-->
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
      width: 80,
      height:80,
    
      quietZone: 0,
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

$("#check_before_submit").click(function(){
  if ($("select[name='gewog']").val()=="" || $("select[name='dzongkhag']").val()=="" || $("select[name='gewog']").val()==null || $("select[name='dzongkhag']").val()==null) {
    alertify("You have not entered your current address properly. Please check and try again.");
  }
  else {
    enteredotp = "singleregister";

    const array = $("#msform").serializeArray(); // Encodes the set of form elements as an array of names and values.
    dependent_list=[];
    const json = {"dependent": JSON.stringify(dependent_list)};
    $.each(array, function () {
      json[this.name] = this.value || "";
    });

    $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",{"data":json, "autoallow":"1", "request":"validate","otp":enteredotp,"cid":"<?php echo $cid;?>"},function(data){
        d=JSON.parse(data);
        if (d.error!==false) {
         alertify(d.error);
        }
        else {
          location.reload();
          //console.log(data);
        }
    });
  }
});



<?php echo isset($generatescript)?$generatescript:"";?>

  </script>
</html>
<?php
$html = ob_get_contents();
if ($cache_ticket) {
  set_cache("TICKET".$cid.$eventid,$html,0);
}
?>