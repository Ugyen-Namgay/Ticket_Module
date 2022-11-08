

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
    
    
        
    
        var btn5 = document.querySelector('.childdetail');
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
          <h2>Please put your children information here</h2>
          <br><div id="child_error" style="position: fixed; top: 15px; color: crimson;"></div><br><hr>
          <input type="text" id="child_firstname" placeholder="First Name" />
          <input type="text" id="child_middlename" placeholder="Middle Name" />
          <input type="text" id="child_lastname" placeholder="Last Name" />
          <input type="date" id="child_dob" placeholder="Date of Birth" max="2022-08-01"/>
        </fieldset>`);
        modalButtonOnly.addFooterBtn('Add', 'tingle-btn tingle-btn--primary tingle-btn--pull-right', function () {
          f=$('#child_firstname').val();
          m=$('#child_middlename').val();
          l=$('#child_lastname').val();
          d=$('#child_dob').val();
          if (f=='' || d=='') {
            
            $("#child_error").html("First name and Date of Birth is mandatory");
            $("#child_error").show(100);
            (f=='')?$('#child_firstname').focus():$('#child_dob').focus();
            setTimeout(()=>{$("#child_error").slideUp(500)},2000);
            return false;
          }
          child_list.push([f,m,l,d]);
          parse_child();
          $('#child_firstname').val('');
          $('#child_middlename').val('');
          $('#child_lastname').val('');
          $('#child_dob').val('');
            modalButtonOnly.close();
        });

        modalButtonOnly.addFooterBtn('Cancel', 'tingle-btn tingle-btn--default tingle-btn--pull-right', function () {
          $('#child_firstname').val('');
          $('#child_middlename').val('');
          $('#child_lastname').val('');
          $('#child_dob').val('');
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


