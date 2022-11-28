
<?php
	
	require_once "utils/sqldb.php";
	$name=isonline();
	if (!$name) {
		Redirect("/login",true);
		exit();
	}
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
    @import url('https://fonts.googleapis.com/css?family=Inconsolata');
    @import url("https://fonts.googleapis.com/css?family=Quicksand&display=swap");

html, body, #drawboard {
  height: 100%;
  width: 100%;
  overflow: hidden;
  font-family: 'Inconsolata';
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

.Participants {
  position: absolute;
  bottom: 30px;
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
<div class="alert simple-alert Winners">
    <h3 id="winnertitle">Winners</h3>
    <div id="winnerbody">
    </div>
</div>

<!-- <div class="alert simple-alert Consolations">
    <h3 id="consolationtitle">Consolation</h3>
    
</div>

<div class="alert success-alert Participants">
</div> -->

  <div id="drawboard">
</div>

<div class="drawbutton">
<a href="#" class="button" onclick="trigger()"><i class=""></i></a>
<div>

</div>



<script src="<?php echo $settings["app"]["homebase"].'/vendors/base/vendor.bundle.base.js'?>"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/draw.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/tingle.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/confetti.js'?>"></script>

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
  slots=[];
  globalcounter = 0;
  drawing = false;
  function drawit(n) {
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
        width: '100px',
        height: '130px',
        blocks: [
          { padding: '10px', background: '#323232', borderRadius: '5px'},
          { padding: '10px', background: '#fff', borderRadius: '5px'  }
        ],
        slots: [
          { order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], direction: direction, speed:(15+i) }
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
          { fonts: [{ text: '9', top: '15%' }] }
        ],
        defaultStyle: {
          borderRadius: Infinity,
          background: '#dfa000',
          fontSize: '32px',
          fontColor: '#333'
        },
        defaultConfig: {
          rowSpacing: '20px',
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
            // $("#d"+i).effect( "highlight", {times:2}, 100+(wavydelay*i )); 
            setTimeout(changeslotcolor(i),100+(waveintensity*i ));
          }
      }

      setTimeout(function(){
        cumulative=0;
        for (i=0; i<digits.length; i++) {
          
          timetostop = cumulative+Math.floor(Math.exp(factor*(digits.length-i)/digits.length)*totaltime/sumofexp);
          cumulative = timetostop;
          console.log(cumulative,i);
          setTimeout(function(){
            slots[globalcounter].stop(parseInt(digits[globalcounter]));
            $("#d"+globalcounter).effect( "bounce", {times:5}, 200); 
            globalcounter++;
            if (globalcounter==digits.length) {
              setTimeout(function(){
                waveflow();
                completed();
                initConfetti();
                render();
              },4000);
            }
          },timetostop);
        }
        drawing = false;
      },delay);

      
      
                  
        
      
    }
//drawit(12125595);
winners = [];
setInterval(function(){
    //{"data":{"winners":[]},"error":false,"message":"Winners list returned"}
    $.get("https://api.bhutanapp.bt/v1.0.1/nationalday/lucky-draw/winners/",function(data){
        d = JSON.parse(data);
        console.log(d.data.winners);
        if (d.data.winners==winners) {
            return 0;
        }
        thewinner = d.data.winners.filter(x => !winners.includes(x));
        winners = d.data.winners;
        drawit(parseInt(thewinner));
    });
},1000);

function trigger() {
    if (drawing) {
        console.log("Already Drawing");
        return false;
    }
    drawing = true;
    //{"data":{"winning_ticket":"259879"},"error":false,"message":"Winning ticket returned!"}
    $.get("https://api.bhutanapp.bt/v1.0.1/nationalday/lucky-draw/select_winner/",function(data){
        console.log(data);
    });
}
 </script>
</html>