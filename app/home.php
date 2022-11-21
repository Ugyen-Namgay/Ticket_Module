<?php
	if (isset($_POST["flush"])) {
		$cache = new Memcached('persistent');
		$cache->flush(0.1);
		echo "OK";
		exit();
	}
	else if (isset($_POST["delete"])) {
		$cache = new Memcached('persistent');
		$cache->delete($_POST["key"]);
		echo "OK";
		exit();
	}
	else if (isset($_POST["realtimestats"])) {
		include_once "utils/memcached.php";
		$ip = '127.0.0.1'; //set your ip address def: 127.0.0.1
		$port = 11211; //set your port def: 112211
		$mem = new Simple_memchached_dashboard($ip,$port);
		$mem->print_event_widget();
		$mem->print_hit_miss_widget();
		$mem->print_memory_widget();
		exit();
	}
	require_once "utils/sqldb.php";
	$name=isonline();
	if (!$name) {
		#Redirect("/",true);
		#exit();
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard</title>
    <!-- base:css -->
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- endinject -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" integrity="sha512-fjy4e481VEA/OTVR4+WHMlZ4wcX/+ohNWKpVfb7q+YNnOCS++4ZDn3Vi6EaA2HJ89VXARJt7VvuAKaQ/gs1CbQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="<?php echo $settings["app"]["logo"]?>" />
	<!-- <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"> -->
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/a5734b29083/integration/bootstrap/3/dataTables.bootstrap.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.core.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.default.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.bootstrap.min.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
	<style type="text/css">
	pre {overflow: auto;width: 100%;}
	.key_scroll{overflow: auto;width: 50%;word-wrap: break-word;margin: 0;float: left;}
	.one_t{width: 30%;}
	.one_h{width: 50%;}
	.top20{margin-top: 30px;}
	</style>
  </head>
  <body>
    <div class="container-scroller">
		<!-- partial:partials/_horizontal-navbar.html -->
    <div class="horizontal-menu">
      <nav class="navbar top-navbar col-lg-12 col-12 p-0" style="background-color:#ffbe0b; min-height: unset; margin-bottom: unset;">
      	<?php include("utils/navbar.php");
		?>
      </nav>
      <nav class="bottom-navbar">
      	<?php include("utils/bottom_navbar.php");?>
      </nav>
    </div>
    <!-- partial -->
		<div class="container-fluid page-body-wrapper">
			<div class="main-panel">
				<div class="content-wrapper">
					<div class="row mt-4">
						<div class="col-lg-6 grid-margin stretch-card">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-lg-12" id="visitor_origin">
											<h4 class="card-title">Visitor Origin</h4>
											<canvas id="hit_by_country"></canvas>
											<p class="mt-3 mb-4 mb-lg-0">Top 5 country wise hits on the site
											</p>
										</div>										
									</div>
								</div>
							</div>
								
						</div>

						<div class="col-lg-6 grid-margin stretch-card">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-lg-12" id="">
											<h4 class="card-title">Cache Stats</h4>
												<div id="realtimestats">
												</div>
												
												
										</div>										
									</div>
								</div>
							</div>
								
						</div>


						<div class="col-lg-12 grid-margin stretch-card">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-lg-12">
										</div>
									</div>
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
    <!-- <script src="vendors/progressbar.js/progressbar.min.js"></script>
		<script src="vendors/chartjs-plugin-datalabels/chartjs-plugin-datalabels.js"></script>
		<script src="vendors/justgage/raphael-2.1.4.min.js"></script-->
	<script src="vendors/justgage/justgage.js"></script>
    <script src="js/jquery.cookie.js" type="text/javascript"></script>
    <!-- Custom js for this page-->
    <script src="js/dashboard.js"></script>
    <!-- End custom js for this page-->
  </body>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js" integrity="sha512-6Cwk0kyyPu8pyO9DdwyN+jcGzvZQbUzQNLI0PadCY3ikWFXW9Jkat+yrnloE63dzAKmJ1WNeryPd1yszfj7kqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script defer src="js/customcharts.js" id="custom_chart"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	<script type="text/javascript" src="//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="//cdn.datatables.net/plug-ins/a5734b29083/integration/bootstrap/3/dataTables.bootstrap.js"></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.min.js"></script>
  <script>

$('#custom_chart').ready(function(){
	//data='[["Bhutan","2"],["Australia","1"]]';
	//COUNTRY WISE VISITOR CHART
	data='<?php echo get("tempvisitors","country,count(ip) as num","NOT country='' GROUP BY country ORDER BY count(ip) DESC LIMIT 5");?>';
	if (data.length>=5) {
		horizontal_bar_chart("hit_by_country",data,"Hits");
	}
	else {
		$("#visitor_origin").hide();
	}
});
  </script>
  <script type="text/javascript">
			jQuery(document).ready(function(){
				$("#stored_keys").dataTable({
					"bFilter":true,
					"bSort":true,
					"dom": '<"top"ilf>rt<"bottom"p><"clear">'
				});
			});

			function deleteKey(id) {
				alertify.confirm("Are you sure?", function (e) {
					if (e) {
						
						$.post("/home",{"delete":"1","key":id},function(k){
							if (k=="OK") {
								window.location.href = "/";
							}
							else {
								alertify.confirm("Could not delete the Key");
							}
						});
					} else {
					// user clicked "cancel"
					}
				});

			}

			function flush(){
				alertify.confirm("Are you sure?", function (e) {
					if (e) {
						
						$.post("/home",{"flush":"1"},function(k){
							if (k=="OK") {
								window.location.href = "/";
							}
							else {
								alertify.confirm("Could not clear the caches");
							}
						});
					} else {
					// user clicked "cancel"
					}
				});
			}
		</script>
</html>