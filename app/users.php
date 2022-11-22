<?php
	
	require_once "utils/sqldb.php";
	$name=isonline();
	if (!$name) {
		Redirect("/",true);
		exit();
	}
?><!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manage Users</title>
    <!-- base:css -->
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
    <!-- <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <link rel="shortcut icon" href="<?php echo $settings["app"]["logo"]?>" />
    <link href="<?php echo $settings["app"]["homebase"].'/css/tingle.min.css'?>" rel="stylesheet">
  </head>
  <body>
    <style>
        .form-control, .form-control-lg, .form-control-sm, select.form-control {
            border: 1px solid #878787; !important
        }
        </style>
    <div class="container-scroller">
		<!-- partial:partials/_horizontal-navbar.html -->
    <div class="horizontal-menu">
      <nav class="navbar top-navbar col-lg-12 col-12 p-0" style="background-color:#ffbe0b; min-height: unset; margin-bottom: unset;">
      	<?php include("utils/navbar.php"); ?>
      </nav>
      <nav class="bottom-navbar">
      	<?php include("utils/bottom_navbar.php"); ?>
      </nav>
    </div>
    <!-- partial -->
		<div class="container-fluid page-body-wrapper">
			<div class="main-panel">
                <div class="content-wrapper">
                <div class="row mt-4">
                        <div class="col-lg-5 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                <?php

                                    $users = get("admin_user","admin_id,email,cid,name,level,created_on");
                                    if ($users=="[]") {
                                        echo '';
                                    }
                                    else {
                                        $users = json_decode($users);
                                        echo '<h4 class="card-title">List of Users</h4>';
                                        echo '<ul class="list-arrow">';
                                        foreach($users as $v) {
                                            echo '<li><a href="#" onclick="edituser('.$v[0].')">'.$v[1].'</a>: '.$v[3].'</li>';
                                        }
                                        echo '</ul>';
                                    }

                                    ?>
                                </div>
                            </div>
                        </div>
						<div class="col-lg-7 grid-margin stretch-card">
                        <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Add Users</h4>
                  <form class="form-sample" id="userform">
                    <p class="card-description">
                    </p>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">Email/Username</label>
                          <input type="hidden" name="admin_id"/>
                          <div class="col-sm-10">
                          <input type="email" name="email" class="form-control" required/>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">CID</label>
                          <div class="col-sm-9">
                            <input type="number" max="99999999999" class="form-control" name="cid" required/>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Full Name</label>
                          <div class="col-sm-9">
                            <input class="form-control" type="text" name="name" required/>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Level</label>
                          <div class="col-sm-9">
                            <select class="form-control form-control-lg" name="level" required>
                            <option value="0" selected>Administrator</option>
                            <option value="1" selected>Checker</option>
                            </select>
                            </div>
                          
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Password</label>
                          <div class="col-sm-9">
                            <input class="form-control" type="password" name="password"/>
                           </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" style="text-align:right">
                            <button type="reset" class="btn btn-light">Clear</button>
                            <button type="submit" class="btn btn-primary me-2">Add</button>
                        </div>
                    </div>
                  </form>
                </div>
              </div>
                        </div>
                </div>
                </div>
				<!-- content-wrapper ends -->
				<!-- partial:partials/_footer.html -->
				<footer class="footer">
          <div class="footer-wrap">
            <?php include("utils/footer.php");?>
          </div>
        </footer>
				<!-- partial -->
			</div>
			<!-- main-panel ends -->
		</div>
		<!-- page-body-wrapper ends -->
    </div>
		<!-- container-scroller -->
    <!-- base:js -->
    <script src="vendors/base/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="js/template.js"></script>
    <!-- endinject -->
    <!-- plugin js for this page -->
    <!-- End plugin js for this page -->
    <script src="vendors/chart.js/Chart.min.js"></script>
    <script src="vendors/progressbar.js/progressbar.min.js"></script>
		<script src="vendors/chartjs-plugin-datalabels/chartjs-plugin-datalabels.js"></script>
		<script src="vendors/justgage/raphael-2.1.4.min.js"></script>
		<script src="vendors/justgage/justgage.js"></script>
    <script src="js/jquery.cookie.js" type="text/javascript"></script>
    <!-- Custom js for this page-->
    <script src="js/dashboard.js"></script>
    <!-- End custom js for this page-->
  </body>
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

    function getFormData($form){
        //var $form = $("#"+formid);
        var unindexed_array = $form.serializeArray();
        var indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    }

$('#userform').on('submit', function(e){
    e.preventDefault();
    data = getFormData($("#userform"));
    if (data.password=="" && data.admin_id=="") {
        alertify("Password cannot be empty for new user");
        return false;
    }
    data = JSON.stringify(data);
        cleandata = data.replace(/,(?!["{}[\]])/g, "");
        $.post("<?php echo $settings["app"]["homebase"].'/submit'?>",{"data":cleandata,"usermanagement":true},function(data){
                d=JSON.parse(data);
                if (d.error) {
                    alertify(d.error);
                }
                else {
                    location.reload();
                }
        });
    
});


function edituser(userid) {
    users=[];
    <?php
    foreach($users as $v) {
        echo 'users['.$v[0].']= {"admin_id":"'.$v[0].'","email":"'.$v[1].'","cid":"'.$v[2].'","name":"'.$v[3].'","level":"'.$v[4].'"};';
    }
    ?>
    $.each(users[userid],function(k,v){
        $("input[name='"+k+"']").val(v);
        $("select[name='"+k+"']").val(v);
    });
    $("button[type='submit']").html("Edit and Save");
}

$("button[type='reset']").click(function(){
    $("input[name='userid']").val('');
    $("button[type='submit']").html("Add");
});
  </script>
</html>