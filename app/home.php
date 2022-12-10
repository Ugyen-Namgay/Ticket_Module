<?php
	require_once "utils/dbconnect.php";

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
		Redirect("/",true);
		exit();
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.0.1/chart.min.js" integrity="sha512-tQYZBKe34uzoeOjY9jr3MX7R/mo7n25vnqbnrkskGr4D6YOoPYSpyafUAzQVjV6xAozAqUFIEFsCO4z8mnVBXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>    <link rel="shortcut icon" href="<?php echo $settings["app"]["logo"]?>" />
	<!-- <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"> -->
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/a5734b29083/integration/bootstrap/3/dataTables.bootstrap.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.core.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.default.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.bootstrap.min.css">


	<!-- google material icons cdn -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

	<!-- goole charts cdn -->
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Pie Chart -->
	<script type="text/javascript">
      	google.charts.load("current", {packages:["corechart"]});
      	//google.charts.setOnLoadCallback(selectYear);
	  	function plotPieChart(chartdata) {
			parsedData = JSON.parse(chartdata);
			
			var data = google.visualization.arrayToDataTable(parsedData);

			var options = {
				title: "Number of Events",
				pieSliceText: "none"
			};

			var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));

			chart.draw(data, options);
		} 
    </script>

	<!-- doughnut chart -->
	<script type="text/javascript">
      	google.charts.load("current", {packages:["corechart"]});
      	//google.charts.setOnLoadCallback(selectYear);
	  	function plotDonutChart(chartdata, Title) {
			parsedData = JSON.parse(chartdata);

			var data = google.visualization.arrayToDataTable(parsedData);

			var options = {
				title: 'Dzongkhag Wise Participants',
				pieHole: 0.6,
				pieSliceText: 'none',
			};
			var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
			chart.draw(data, options);
		} 
    </script>
	<!-- line chart -->
	<!-- <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      //google.charts.setOnLoadCallback(selectYear);	
	function plotLineChart(chartdata,Title) {
		parsedData=JSON.parse(chartdata);
		temp_header = [];
		$.each(parsedData,function(k,v){
			temp_header.push(k);
		});
		mainData = [];
		mainData.push(temp_header);
		for (i=0; i<parsedData[temp_header[0]].length;i++) {	
			temp_values=[];
			$.each(parsedData,function(k,v){
				temp_values.push(parsedData[k][i]);
			});
			mainData.push(temp_values);
		}
        var data = google.visualization.arrayToDataTable(mainData);
        var options = {
          title: "Participants Each Year",
          curveType: 'line',
          legend: { position: 'bottom' }
        };
        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        chart.draw(data, options);
	}
    </script> -->

	<!-- line chart -->
	<script type="text/javascript">
	// 	// google.charts.load('current', {'packages':['bar']});
    //   google.charts.load("current", {packages:["corechart"]});
    //   //google.charts.setOnLoadCallback();
	//   function plotLineChart(chartdata, title) {
	// 	parsedData=JSON.parse(chartdata);
		
	// 	var data = google.visualization.arrayToDataTable(parsedData);
        
    //     var options = {
	// 		chart: {
	// 			title: 'Event Participants',
	// 			subtitle: 'No. of participants in each events',
				

	// 		},
	// 		bars: 'vertical',
	// 		legend: {position: 'top', maxLines: 3},
	// 		vAxis: {
	// 			format: 'decimal',	
	// 		}
		  
	// 	};

    //     var chart = new google.charts.Bar(document.getElementById('curve_chart'));

    //     chart.draw(data, google.charts.Bar.convertOptions(options));
    //   }
</script>

	<!-- column chart -->
	<script type="text/javascript">
      google.charts.load('current', {'packages':['bar']});
      //google.charts.setOnLoadCallback(selectYear);

      function plotColumnChart(chartdata,Title) {
		parsedData=JSON.parse(chartdata);
		temp_header = [];
		$.each(parsedData,function(k,v){
			temp_header.push(k);
		});
		mainData = [];
		mainData.push(temp_header);
		for (i=0; i<parsedData[temp_header[0]].length;i++) {	
			temp_values=[];
			$.each(parsedData,function(k,v){
				temp_values.push(parsedData[k][i]);
			});
			mainData.push(temp_values);
		}
        var data = google.visualization.arrayToDataTable(mainData);
        var options = {
				chart: {title: 'Event Participants Each Year',
			},
		  bars: 'horizontal'
        };
		var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
		// document.getElementById('year').onchange = selectYear() {
        //    options['vAxis']['value'] = this.value;
        //    chart.draw(data, options);
		//    chart.draw(data, google.charts.Bar.convertOptions(options));
        //  };
	}
    </script>

	<!-- end of charts -->

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
	<style type="text/css">
	svg {
		border-radius: 10px !important;
	}
	pre {overflow: auto;width: 100%;}
	.key_scroll{overflow: auto;width: 50%;word-wrap: break-word;margin: 0;float: left;}
	.one_t{width: 30%;}
	.one_h{width: 50%;}
	.top20{margin-top: 30px;}

		.container1{
			display:grid;
			width:100%;
			margin: 0 auto;	
			grid-template-columns: repeat(4, 1fr);
			grid-template-rows: 1fr;
			margin-top:1rem;
			gap: 1rem;
			text-align: center;
		}
		.container1 > div{
			background-color: #FFFFFF;
			padding:.5rem;
			border-radius:10px;
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			grid-template-rows: 1fr;
			grid-column-gap: 0px;
			grid-row-gap: 0px;
			padding: .8rem 1rem 0rem 0rem;
		}
		.container1 > div > div >i{
			font-size:2rem;
			vertical-align: middle;
			text-align: right;
		}
		.container1 > div > div> p{
			font-size:1.5rem;
			vertical-align: middle;
		}
		.container1 > div > div > b{
			font-size:1rem;
			vertical-align: middle;
		}
		main{
			margin-top: .4rem;
			height: 100px;
			text-align:left;
		}
		main .date{
			border-radius: 2.4rem;
		}
		main .insights{
			display:grid;
			grid-template-columns: repeat(2, 1fr);
			gap:1rem;
		}
		main .insights > div {
			display:grid;
			gap:1rem;
			/* border: solid .5px; */
			margin-top: 1rem;
			box-shadow: none;
			border-radius: 10px;
			width: 690px;
			height: 250px;
			border-radius: 10px;
		}
		.main-panel1{
			padding-left:1rem;
			background-color: #f0f3f6;
		}

		/* ===== Media Queries for Tablets ===== */
		@media screen and (max-width: 1200px){

			.container1 > div{
				width: 275px;
			}
			main .insights{
				display:grid;
				grid-template-columns: repeat(2, 1fr);
				gap:.5rem;
			}
			main .insights > div {
				display:grid;
				gap:1rem;
				padding: 0px;
				margin-top: 1rem;
				box-shadow: none;
				border-radius: 1px;
				transition: all 300ms ease;
				width: 560px;
				height: 250px;
			}
		}
		/* ===== Media Queries for Mobile Phones ===== */
		@media screen and (max-width: 768px){
			.container1 {
				width: 100%;
				grid-template-columns: 1fr;
			}
			.container1 > div {
				width: 100%;
				grid-template-columns: 1fr;
			}
			.container1 > div{
			background-color: #FFFFFF;
			padding:.5rem;
			border-radius:10px;
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			grid-template-rows: 1fr;
			grid-column-gap: 0px;
			grid-row-gap: 0px;
			width: 98%;
			text-align:center;
			/* padding: .8rem 1rem 0rem 0rem; */
		}
		.container1 > div > div{
					
		}
			main{
				margin-top: 2rem;
				padding: 0 1rem;
				height: auto;
			}
			.main-panel1 {
    			padding-top: none;
				padding-left: 0px;
 			}
			 main .insights{
			display:grid;
			grid-template-columns: repeat(1, 1fr);
			gap:0rem;
			
			}
			main .insights > div {
				display:grid;
				gap:0rem;
				margin-top: 1rem;
				box-shadow: none;
				border-radius: 1px;
				transition: all 300ms ease;
				width: 390px;
				height: 200px;
			}
			main .insights > div:last-child{
				margin-bottom:1rem;
			}
		}
		select{
			border-radius:20px;
			/* background-color: #f0f3f6; */
		}
		select > option{
			font-color: #6c7293;
		}
		.dropdown1{
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			grid-template-rows: 1fr;
			gap: 0px;
		}

		.card-header {
			display: flex;
			align-items: center;
    		justify-content: space-between;
			background: white;
		}
		
		.row {
			margin: 20px;
		}

		#year {
			width: 100%;
			padding: 10px;
			/* color: black; */
			font-weight: 900;
			text-align: center;
			border-radius: 4px;
			border: 1px;
		}

		.selectyear {
			display: block;
			text-align: right;
			font-weight: 900;
			background: transparent;
		}
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
	<div class="main-panel">
            <div class="content-wrapper container">
				<div class="row">
					<div class="col-md-6">
						<h5 class="card-header selectyear">Select Year: </h5>
					</div>
					<div class="col-md-6">
						<div>
							<select class="form-control form-control-select" id="year" onchange="selectYear()">
							<!-- <option value="" disabled selected hidden>Select Year</option> -->
							<?php
								$conn = new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
								$displayYear = $conn -> query("SELECT DISTINCT EXTRACT(YEAR FROM start_datetime) AS years FROM events ORDER BY start_datetime DESC");
								while($row = $displayYear -> fetch_assoc()){
									echo "<option value='". $row['years'] ."'>" .$row['years'] ."</option>";
								}
							?>
							</select>
						</div>
					</div>
				</div>


				<div class="row">
					<div class="col-md-3">
					<div class="card">
						<h5 class="card-header"><i class="material-icons" style="color:#EFC050">event</i> Events: <span class="card-text" id="events">Loading...</span></h5>
					</div>
					</div>
					<div class="col-md-3">
					<div class="card">
						<h5 class="card-header"><i class="material-icons" style="color:#FA7A35">groups</i> Users: <span class="card-text" id="users">Loading...</span></h5>
					</div>
					</div>
					<div class="col-md-3">
					<div class="card">
						<h5 class="card-header"><i class="material-icons" style="color:#D65076">people_alt</i> Submission: <span class="card-text" id="participants">Loading...</span></h5>
					</div>
					</div>
					<div class="col-md-3">
					<div class="card">
						<h5 class="card-header"><i class="material-icons" style="color:#00A591">public</i> Locations: <span class="card-text" id="dzongkhags">Loading...</span></h5>
					</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="card">
							<div class="card-body">
								<div class="insights">
									<div id="piechart_3d"></div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="card">
							<div class="card-body">
								<div class="insights">
									<div id="donutchart"></div>	
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
									<div class="insights">
										<div id="livecapacity"></div>
									</div>
								</div>
							</div>
						</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="insights">
									<div id="columnchart_material"></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- <div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="insights">
									<div id="curve_chart"></div> 
								</div>
							</div>
						</div>
					</div>
				</div> -->
			</div>
    <!-- partial -->
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

<!-- Script for charts -->
<!-- Script for displaying event for Particular Year -->
<script type="text/javascript">
	function selectYear(){
		$.ajax({
			url:"/dashboard",
			// dataType: "json",
			method: "POST",
			data: {
				year : document.getElementById("year").value,
				dataType: "Year",
			},
			success:function(data){
				data = JSON.parse(data);
				$("#events").html(data.event_Count.event_Count);
				$("#users").html(data.registered_User.registered_User);
				$("#participants").html(data.event_Participants.event_Participants);
				$("#dzongkhags").html(data.dzongkhag_Count.dzongkhag_Count);
			}
			}
		);

		$.ajax({
       		url:"/dashboard",
			method:"POST",
			data:{
				year: document.getElementById("year").value,
				chartType: "ColumnChart"
			},
			success:function(data)
			{
				plotColumnChart(data);
			}
    	});

		// $.ajax({
       	// 	url:"/dashboard",
		// 	method:"POST",
		// 	data:{
		// 		year: document.getElementById("year").value,
		// 		chartType: "LineChart"
		// 	},
		// 	success:function(data)
		// 	{
		// 		plotLineChart(data);
		// 	}
    	// });

		$.ajax( {
				url: "/dashboard",
				method: "POST",
				data: {
					year: document.getElementById("year").value,
					chartType: "PieChart"
				},
				success:function(data) {
					plotPieChart(data);
				}
			});

			$.ajax( {
				url: "/dashboard",
				method: "POST",
				data: {
					year: document.getElementById("year").value,
					chartType: "DonutChart"
				},
				success:function(data) {
					plotDonutChart(data);
				}
			});
	};
	//selectYear();
	$(document).ready(function(){
		$('#year').change(function(){
			var year = $(this).val();
		});
		setTimeout(selectYear,400);
	});
</script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js" integrity="sha512-6Cwk0kyyPu8pyO9DdwyN+jcGzvZQbUzQNLI0PadCY3ikWFXW9Jkat+yrnloE63dzAKmJ1WNeryPd1yszfj7kqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script defer src="js/customcharts.js" id="custom_chart"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	<script type="text/javascript" src="//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script>
	<!-- <script type="text/javascript" src="//cdn.datatables.net/plug-ins/a5734b29083/integration/bootstrap/3/dataTables.bootstrap.js"></script> -->
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/alertify.js/0.3.11/alertify.min.js"></script>
  <script>

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

<script>
	//WRITTEN BY PHUNTSHO
	// Load the Google Charts library
google.charts.load('current', {'packages':['corechart']});

// Set a callback function to run when the Google Charts library is loaded
google.charts.setOnLoadCallback(drawLiveUpdate);

// Define the callback function that will create and populate the chart
function drawLiveUpdate() {
  // Create the data table
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Event Name');
  data.addColumn('number', 'Current Registrations');
  data.addColumn('number', 'Capacity');

  // Query the events and registration_requests tables to get the data for the chart
  $.post('/dashboard/', {"liveupdate":"1"}, function(livedata) {
	response = JSON.parse(livedata);
    var rows = [];
    response.forEach(function(item) {
      rows.push([item.name, parseInt(item.current_registrations), parseInt(item.capacity)]);
    });
    data.addRows(rows);

    // Set the chart options
    var options = {
      title: 'Current Registrations for All Events',
      hAxis: {title: 'Event Name'},
      vAxis: {title: 'Current Registrations'},
      seriesType: 'bars',
      series: {
		0: {
		type: 'bar'
    	},
		1: {
			type: 'bar'
		}
	}
	};

    // Create and draw the chart
    var chart = new google.visualization.ComboChart(document.getElementById('livecapacity'));
    chart.draw(data, options);
  	});

	// Set the chart to refresh every 10 seconds
	setInterval(function() {
	$.post('/dashboard/', {"liveupdate":"1"}, function(livedata) {
		response = JSON.parse(livedata);
		var rows = [];
		response.forEach(function(item) {
		rows.push([item.name, parseInt(item.current_registrations), parseInt(item.capacity)]);
		});
		data.addRows(rows);
		var chart = new google.visualization.ComboChart(document.getElementById('livecapacity'));
		chart.draw(data, options);
	});
	}, 10000);
}

	</script>
</html>