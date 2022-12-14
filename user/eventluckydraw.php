
<?php
	require_once "utils/sqldb.php";
	$name=isonline();
	if (!$name) {
		Redirect("/",true);
		exit();
	}

    if (!isset($args[0]) && !is_numeric($args[0])) {
        echo "INVALID EVENT ID FOR THE DRAW";
        exit();
    }
    $eventid = $args[0];
    $eventdetail = json_decode(get("events","*","id=$eventid"),true);
    
    ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="<?php echo $settings["app"]["homebase"].'/css/tingle.min.css'?>" rel="stylesheet">
    <title>Lucky Draw</title>
    <link rel="shortcut icon" href="<?php echo $settings["app"]["homebase"]."/".$settings["app"]["logo"]?>" />
  </head>
  <style>
@font-face {
      font-family: "bhutanfont";
      src: url("/resources/custom_font.woff") format("woff");
     }

html, body, #drawboard {
  height: 100%;
  width: 100%;
  overflow: hidden;
  font-family: 'bhutanfont';
  /*background-image: url("<?php echo $settings["app"]["homebase"]."/"?>images/punakha.jpg")*/
  background-color: #fcba03;
  display: flex;
    justify-content: center;
}

 #drawboard {
  font-weight: bold;
  font-size: 16vmin;
  text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
  fill: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  position: absolute;
    top: 0px;
    background: transparent;
 }


 .alert {
  width: 50%;
  padding: 10px;
  border-radius: 5px;
  /* box-shadow: 0 0 15px 5px #ccc; */
}

.close {
  position: absolute;
  width: 30px;
  height: 30px;
  opacity: 0.5;
  border-width: 1px;
  border-style: solid;
  border-radius: 50%;
  right: 15px;
  top: 25px;
  text-align: center;
  font-size: 1.6em;
  cursor: pointer;
}

.simple-alert {
  background-color: #ebebeb;
  border-left: 5px solid #6c6c6c;
}
.simple-alert .close {
  border-color: #6c6c6c;
  color: #6c6c6c;
}

.success-alert {
  background-color: #a8f0c6;
  border-left: 5px solid #178344;
}
.success-alert .close {
  border-color: #178344;
  color: #178344;
}

.danger-alert {
  background-color: #f7a7a3;
  border-left: 5px solid #8f130c;
}
.danger-alert .close {
  border-color: #8f130c;
  color: #8f130c;
}

.warning-alert {
  background-color: #ffd48a;
  border-left: 5px solid #8a5700;
}
.warning-alert .close {
  border-color: #8a5700;
  color: #8a5700;
}

.alert h3 {
  font-family: Quicksand;
}
.Winners {
  position: absolute;
  top: 30px;
  left: 10px;
  width: 200px;
}

.Consolations {
  position: absolute;
  top: 30px;
  right: 10px;
  width: 200px;
}


.button {
  width: 72px;
  height: 72px;
  line-height: 72px;
  display: block;
  position: relative;
  -moz-border-radius: 50%;
  -webkit-border-radius: 50%;
  border-radius: 50%;
  border: 0px solid #444;
  text-align: center;
  display: inline-block;
  vertical-align: middle;
  position: relative;
  z-index: 10;
  color: #333;
}

.button:hover {
  color: #fff;
}

.button:after {
    position: absolute !important;
  content: "";
  width: 56px;
  height: 56px;
  display: block;
  position: relative;
  -moz-border-radius: 50%;
  -webkit-border-radius: 50%;
  border-radius: 50%;
  right: 8px;
  top: 8px;
  background-color: #333;
  visibility: hidden;
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=0);
  opacity: 0;
  -moz-transition: all 0.4s ease;
  -o-transition: all 0.4s ease;
  -webkit-transition: all 0.4s ease;
  transition: all 0.4s ease;
  opacity: 1\9;
  visibility: visible\9;
  display: none\9;
  -moz-transform: scale(0.5, 0.5);
  -ms-transform: scale(0.5, 0.5);
  -webkit-transform: scale(0.5, 0.5);
  transform: scale(0.5, 0.5);
  z-index: -1;
  -moz-transition: all 0.2s ease;
  -o-transition: all 0.2s ease;
  -webkit-transition: all 0.2s ease;
  transition: all 0.2s ease;
}

.button:hover:after {
  visibility: visible;
  filter: progid:DXImageTransform.Microsoft.Alpha(enabled=false);
  opacity: 1;
  display: block\9;
  -moz-transform: scale(1, 1);
  -ms-transform: scale(1, 1);
  -webkit-transform: scale(1, 1);
  transform: scale(1, 1);
}

.drawbutton {
    position: absolute;
    bottom: 30px;
    left: calc(50% - 36px);
}
.drawbutton a {
    width: 72px;
    text-decoration: none;
    background-color: #ff5456;
}

.confetti{
   top: 0;
   left: 0;
   display: block;
   user-select: none;
}

.sign {
  justify-content: center;
  align-items: center;
  position: absolute;
  top: 100px;
}

.sign span {

font-size: 2.6rem;
text-align: center;
line-height: 1;
color: #c6e2ff;
text-shadow: 0 0 2px black;
/* webkit-animation: neon 1s ease-in-out infinite alternate;
animation: neon 1s ease-in-out infinite alternate; */
}

/*-- Animation Keyframes --*/

/*-- Animation Keyframes --*/
@-webkit-keyframes neon {
  from {
    text-shadow: 0 0 6px rgba(234, 204, 70, 0.92), 0 0 30px rgba(234, 204, 70, 0.34), 0 0 12px rgba(234, 204, 70, 0.52), 0 0 21px rgba(234, 204, 70, 0.92), 0 0 34px rgba(234, 204, 70, 0.78), 0 0 54px rgba(234, 204, 70, 0.92);
  }
  to {
    text-shadow: 0 0 6px rgba(229, 213, 139, 0.98), 0 0 30px rgba(229, 213, 139, 0.42), 0 0 12px rgba(229, 213, 139, 0.58), 0 0 22px rgba(229, 213, 139, 0.84), 0 0 38px rgba(229, 213, 139, 0.88), 0 0 60px #e5d58b;
  }
}
@keyframes neon {
  from {
    text-shadow: 0 0 6px rgba(234, 204, 70, 0.92), 0 0 30px rgba(234, 204, 70, 0.34), 0 0 12px rgba(234, 204, 70, 0.52), 0 0 21px rgba(234, 204, 70, 0.92), 0 0 34px rgba(234, 204, 70, 0.78), 0 0 54px rgba(234, 204, 70, 0.92);
  }
  to {
    text-shadow: 0 0 6px rgba(229, 213, 139, 0.98), 0 0 30px rgba(229, 213, 139, 0.42), 0 0 12px rgba(229, 213, 139, 0.58), 0 0 22px rgba(229, 213, 139, 0.84), 0 0 38px rgba(229, 213, 139, 0.88), 0 0 60px #e5d58b;
  }
}


.link {
  position: absolute;
  bottom: 10px;
  left: 10px;
  color: #828282;
  text-decoration: none;
}
.link:focus, .link:hover {
  color: #c6e2ff;
  text-shadow: 0 0 2px rgba(202, 228, 225, 0.92), 0 0 10px rgba(202, 228, 225, 0.34), 0 0 4px rgba(30, 132, 242, 0.52), 0 0 7px rgba(30, 132, 242, 0.92), 0 0 11px rgba(30, 132, 242, 0.78), 0 0 16px rgba(30, 132, 242, 0.92);
}
</style>
  <body>
  <canvas class="confetti" id="confetti"></canvas>

<div class="sign">
    <h1 style="text-align:center; font-size: xxx-large; margin-top:-30px"><?php echo $eventdetail[0]["name"];?><div style="font-size: 0.5em;">Lucky Draw</div></h1>
    
  <span id="winnerannouncement"></span></div>
<!-- <div class="alert warning-alert Winners">
    <h3 id="winnertitle">Winners</h3>
    <div id="winnerbody">
    </div>
</div>

<div class="alert simple-alert Consolations">
    <h3 id="consolationtitle">Consolation</h3>
    <div id="consolationbody">
    </div>
</div> -->

  <div id="drawboard">
</div>

</div>



<script src="<?php echo $settings["app"]["homebase"].'/vendors/base/vendor.bundle.base.js'?>"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/draw.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/tingle.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/confetti.js'?>"></script>

<script>
//audio_slotmachine = new Audio("/resources/slotmachine.wav");
audio_slotmachine = new Audio("/resources/slots_demo_mono.mp3");
audio_finalslot = new Audio("/resources/finalslot.wav");
audio_tada = new Audio("/resources/tada.mp3");
audio_firecracker = new Audio("/resources/firecracker.mp3");
audio_backmusic= new Audio("/resources/Backmusic.mp3");

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
  slots=[];
  globalcounter = 0;

  function drawit(n) {
    //audio_backmusic.pause();
    audio_slotmachine.play();
    $("#drawboard").html("");
    //https://100px.net/docs/slot.html
    drawboard = document.querySelector("#drawboard");
    globalcounter = 0;
    digits=(n+"").split("");
    totaltime=10000;
    waveintensity=100;
    sumofexp=0;
    factor = -2;
    for (i=0; i<digits.length; i++) {
      sumofexp+=(Math.exp(factor*(digits.length-i)/digits.length));
    }
    console.log(sumofexp);

    delay = 5000;
    direction = 1;
    for (i=0; i<digits.length; i++) {
      child = document.createElement("div");
      child.setAttribute("id", "d"+i);
      drawboard.appendChild(child);
      direction = direction*1;
      slots[i] = new LuckyCanvas.SlotMachine(("#d"+i),{
        width: '200px',
        height: '330px',
        blocks: [
          { padding: '10px', background: '#323232', borderRadius: '5px'},
          { padding: '10px', background: '#fff', borderRadius: '5px'  }
        ],
        slots: [
          { order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35], direction: direction, speed:(5+(digits.length-i)) }
        ],
        prizes: [
          { fonts: [{ text: '0', top: '15%' }] },
          { fonts: [{ text: '1', top: '15%' }] },
          { fonts: [{ text: '2', top: '15%' }] },
          { fonts: [{ text: '3', top: '15%' }] },
          { fonts: [{ text: '4', top: '15%' }] },
          { fonts: [{ text: '5', top: '15%' }] },
          { fonts: [{ text: '6', top: '15%' }] },
          { fonts: [{ text: '7', top: '15%' }] },
          { fonts: [{ text: '8', top: '15%' }] },
          { fonts: [{ text: '9', top: '15%' }] },
          { fonts: [{ text: 'A', top: '15%' }] },
          { fonts: [{ text: 'B', top: '15%' }] },
          { fonts: [{ text: 'C', top: '15%' }] },
          { fonts: [{ text: 'D', top: '15%' }] },
          { fonts: [{ text: 'E', top: '15%' }] },
          { fonts: [{ text: 'F', top: '15%' }] },
          { fonts: [{ text: 'G', top: '15%' }] },
          { fonts: [{ text: 'H', top: '15%' }] },
          { fonts: [{ text: 'I', top: '15%' }] },
          { fonts: [{ text: 'J', top: '15%' }] },
          { fonts: [{ text: 'K', top: '15%' }] },
          { fonts: [{ text: 'L', top: '15%' }] },
          { fonts: [{ text: 'M', top: '15%' }] },
          { fonts: [{ text: 'N', top: '15%' }] },
          { fonts: [{ text: 'O', top: '15%' }] },
          { fonts: [{ text: 'P', top: '15%' }] },
          { fonts: [{ text: 'Q', top: '15%' }] },
          { fonts: [{ text: 'R', top: '15%' }] },
          { fonts: [{ text: 'S', top: '15%' }] },
          { fonts: [{ text: 'T', top: '15%' }] },
          { fonts: [{ text: 'U', top: '15%' }] },
          { fonts: [{ text: 'V', top: '15%' }] },
          { fonts: [{ text: 'W', top: '15%' }] },
          { fonts: [{ text: 'X', top: '15%' }] },
          { fonts: [{ text: 'Y', top: '15%' }] },
          { fonts: [{ text: 'Z', top: '15%' }] }
        ],
        defaultStyle: {
          borderRadius: Infinity,
          background: '#dfa000',
          fontSize: '82px',
          fontColor: '#333'
        },
        defaultConfig: {
          rowSpacing: '80px',
          colSpacing: '10px'
        },
        end (prize) {
          // console.log(prize)
        }
      });
      slots[i].play();

      }

      function changeslotcolor(k,color='#dfa000') {
        slots[k].blocks=[{ padding: '10px', background: color,  borderRadius: Infinity },
          { padding: '10px', background: '#fff',  borderRadius: Infinity }];
      }
      function waveflow() {
        for (i=0; i<digits.length; i++) {
            $("#d"+i).effect( "bounce", {times:1}, 100+(waveintensity*i )); 
            //$("#d"+i).effect( "highlight", {times:2}, 100+(waveintensity*i )); 
            setTimeout(changeslotcolor(i),100+(waveintensity*i ));
          }
      }
      stack = ["0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
      
      setTimeout(function(){
        cumulative=0;
        for (i=0; i<digits.length; i++) {
          
          timetostop = cumulative+Math.floor(Math.exp(factor*(digits.length-i)/digits.length)*totaltime/sumofexp);
          cumulative = timetostop;
          console.log(cumulative,i);
          setTimeout(function(){
            if (digits.length-globalcounter>4) {
                audio_slotmachine.currentTime="11";
            }
            // else {
            //     audio_slotmachine.currentTime="11";
            // }
            
            audio_finalslot.pause();
            audio_finalslot.currentTime="0";
            audio_finalslot.play();
            slots[globalcounter].stop(stack.indexOf(digits[globalcounter].toString()));
            $("#d"+globalcounter).effect( "bounce", {times:5}, 200); 
            globalcounter++;
            if (globalcounter==digits.length) {
              setTimeout(function(){
                audio_slotmachine.pause();
                audio_finalslot.play();
                audio_tada.play();
                audio_backmusic.currentTime="0";
                audio_backmusic.play();
                waveflow();
                initConfetti();
                setTimeout(function(){audio_firecracker.play();},1000);
                render();
              },4000);
            }
          },timetostop);
        }
      },delay); 
      
    }


    var winners=[];
    $.post("/luckydrawevent",{"winners":"get","eventid":"<?php echo $eventid;?>"},function(data){ //First time load all winners
            //console.log(data);
            d = JSON.parse(data);
            if (d.length==winners.length) {
                return 0;
            }
            thewinner = d.filter(x => !winners.includes(x));
            winners = d;
    });

    setInterval(function(){
        //{"data":{"winners":[]},"error":false,"message":"Winners list returned"}
        $.post("/luckydrawevent",{"winners":"get","eventid":"<?php echo $eventid;?>"},function(data){
            //console.log(data);
            d = JSON.parse(data);
            if (d.length==winners.length) {
                return 0;
            }
            thewinner = d.filter(x => !winners.includes(x));
            winners = d;
            console.log(thewinner);
            drawit((thewinner));
        });
    },3000);
 </script>
</html>