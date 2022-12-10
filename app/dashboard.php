<?php
	require_once "utils/sqldb.php";
    //$data=[];
	$conn = new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
    if (isset($_POST['liveupdate'])) {
        $query = "SELECT e.name,
                        IFNULL(SUM(LENGTH(r.other_cids) - LENGTH(REPLACE(r.other_cids, ';', ''))+ 1), 0) AS current_registrations,
                        e.capacity
                FROM events e
                LEFT JOIN registration_requests r ON e.id = r.event_id
                GROUP BY e.name, e.capacity;";
        $result = $conn->query($query);
        // Return the data as a JSON array
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit();
    }
    else if(isset($_POST['year'])){
        $year = $_POST['year'];

        if (isset($_POST["dataType"]) && $_POST["dataType"] == "Year"){
            $result = $conn -> query("SELECT COUNT(*) AS event_Count FROM events WHERE YEAR(start_datetime) = '$year' ");
            $data['event_Count'] = $result -> fetch_assoc();

            //$data['event_Count']=json_decode(get("events","COUNT(*) AS event_Count","YEAR(start_datetime)='$year'",true),true)[0]["event_Count"];

            $result = $conn -> query("SELECT IFNULL(SUM(LENGTH(other_cids) - LENGTH(REPLACE(other_cids, ';', ''))+ 1), 0) AS registered_User FROM registration_requests WHERE YEAR(register_datetime) = '$year' ");
            $data['registered_User'] = $result -> fetch_assoc();
            //$data['registered_User']=json_decode(get("registration_requests","IFNULL(SUM(LENGTH(other_cids) - LENGTH(REPLACE(other_cids, ';', ''))+ 1), 0) AS registered_User","YEAR(start_datetime)='$year'"),true)[0]["registered_User"];


            $result = $conn -> query("SELECT IFNULL(COUNT(DISTINCT(dzongkhag)), 0) as dzongkhag_Count FROM registration_requests WHERE is_allowed = '1' AND YEAR(register_datetime)='$year'");
            $data['dzongkhag_Count'] = $result -> fetch_assoc();
            //$data['dzongkhag_Count']=json_decode(get("registration_requests","IFNULL(COUNT(DISTINCT(dzongkhag)), 0) as dzongkhag_Count","YEAR(start_datetime)='$year' AND is_allowed = '1'"),true)[0]["dzongkhag_Count"];

            $result = $conn -> query("SELECT IFNULL(SUM(LENGTH(other_cids) - LENGTH(REPLACE(other_cids, ';', ''))+ 1), 0) AS event_Participants FROM registration_requests WHERE YEAR(register_datetime) = '$year' AND is_allowed = '1' ");
            $data['event_Participants'] = $result -> fetch_assoc();
            //$data['event_Participants']=json_decode(get("registration_requests","IFNULL(SUM(LENGTH(other_cids) - LENGTH(REPLACE(other_cids, ';', ''))+ 1), 0) AS event_Participants","YEAR(start_datetime)='$year' AND is_allowed = '1'"),true)[0]["event_Participants"];

            echo json_encode($data);
        }

        if (isset($_POST["chartType"]) && $_POST["chartType"] == "PieChart") {
            $result = $conn -> query("SELECT address, COUNT(address) AS address_count FROM events WHERE YEAR(start_datetime)='$year' GROUP BY address;");
            if ($result -> num_rows > 0) {
                $arr = array(
                    "Address",
                    "Event Count"
                );
                $array[] = $arr;
                while($row = $result -> fetch_assoc()) {  
                    $arr = array(
                        $orgname = $row['address'],
                        $count = (int)$row['address_count'],
                    );
                    $array[] = $arr;
                }
                echo json_encode($array);
            }
        }
        
        if (isset($_POST["chartType"]) && $_POST["chartType"] == "DonutChart") {
            $result = $conn -> query("SELECT dzongkhag, SUM(LENGTH(other_cids) - LENGTH(REPLACE(other_cids, ';', ''))+ 1) AS dzongkhag_count FROM registration_requests WHERE YEAR(register_datetime) = '$year' AND is_allowed='1' GROUP BY dzongkhag; ");
            if ($result -> num_rows > 0) {
                $arr = array(
                    "Dzongkhag",
                    "Participant Count"
                );
                $array[] = $arr;
                while($row = $result -> fetch_assoc()) {  
                    $arr = array(
                        $orgname = $row['dzongkhag'],
                        $count = (int)$row['dzongkhag_count'],
                    );
                    $array[] = $arr;
                }
                echo json_encode($array);
            }
        }
    }

    // if (isset($_POST["chartType"]) && $_POST["chartType"] == "LineChart") {
    //     $males=0;
    //     $females=0;
    //     $minors=0;
    //     $result = $conn -> query("SELECT cid,other_cids FROM registration_requests");
    //     while ($citizens = $result -> fetch_assoc()) {
    //         $individuals = explode(";",ltrim($citizens["other_cids"],";"));
    //         $individuals[]=$citizens["cid"];
    //         foreach($individuals as $cid) {
    //             if (strpos($cid,"minor")!=false) {
    //                 $minors++;
    //                 continue;
    //             }
    //             $sub_result = $conn ->query("SELECT gender FROM citizens WHERE cid='$cid';");
    //             if ($sub_result->num_rows>0 && $sub_result->fetch_assoc()["gender"]=="M") {
    //                 $males++;
    //             }
    //             else {
    //                 $females++;
    //             }
    //         }
    //     }
    //    echo '{"Year":['.$year.'],"Males":['.$males.'],"Females":['.$females.'],"Minors":['.$minors.']}';
    //     exit();
    // }

    // if (isset($_POST["chartType"]) && $_POST["chartType"]=="LineChart") {
    //     // $males=0;
    //     // $females=0;
    //     // $minors=0;
    //     $result = $conn -> query("SELECT A.name, B.event_id, C.cid, C.gender, D.cid FROM events as A LEFT JOIN registration_requests AS B ON A.id = B.event_id LEFT JOIN citizens as C ON C.cid = B.cid LEFT JOIN minor AS D ON C.cid = D.parent_cid; " );
    //     // $year = implode(";", $result -> fetch_assoc("Year"));
    //     // if ($result -> num_rows > 0) {
    //         $arr = array(
    //             "Event Name",
    //             "Minor Count",
    //             "Male Count",
    //             "Female Count"
    //         );
    //         $array[] = $arr;
    //         $row = $result -> fetch_assoc();
    //         // while($row = $result -> fetch_assoc()) {  
    //             $sub_queryEvent = $conn -> query("SELECT name FROM events");
    //             $sub_queryEvent = $sub_queryEvent -> fetch_assoc()["name"];
    //             $sub_queryMinor = $conn -> query("SELECT COUNT(cid) FROM minor");
    //             $sub_queryMinor = $sub_queryMinor -> fetch_assoc()["COUNT(cid)"];
    //             $sub_queryMale = $conn -> query("SELECT COUNT(gender) FROM citizens WHERE gender='M'");
    //             $sub_queryMale = $sub_queryMale -> fetch_assoc()["COUNT(gender)"];
    //             $sub_queryFemale = $conn -> query("SELECT COUNT(gender) FROM citizens WHERE gender='F'");
    //             $sub_queryFemale = $sub_queryFemale -> fetch_assoc()["COUNT(gender)"];
                
                
    //             $arr = array(
    //                 $eventName = $sub_queryEvent,
    //                 $minorsCount = (int)$sub_queryMinor,
    //                 $malesCount = (int)$sub_queryMale,
    //                 $femalesCount = (int)$sub_queryFemale,
    //             );
    //             $array[] = $arr;
    //         echo json_encode($array);
    // }

    if (isset($_POST["chartType"]) && $_POST["chartType"] == "ColumnChart") {
        $males=0;
        $females=0;
        $minors=0;
        //$result = $conn -> query("SELECT cid,other_cids FROM registration_requests WHERE YEAR(register_datetime) = '$year'");
        $result = json_decode(get("registration_requests","cid,other_cids","YEAR(register_datetime)='$year'",true),true);
        foreach($result as $citizens) {
            $individuals = explode(";",ltrim($citizens["other_cids"],";"));
            $individuals[]=$citizens["cid"];
            foreach($individuals as $cid) {
                if (substr($cid,0,1)=="7") {
                    $minors++;
                    continue;
                }
                $sub_result = json_decode(get("citizens","*","cid='$cid'",true),true);
                if (!empty($sub_result) && $sub_result["gender"]=="M") {
                    $males++;
                }
                else {
                    $females++;
                }
            }
        }
       echo '{"Year":["'.$year.'"],"Males":['.$males.'],"Females":['.$females.'],"Minors":['.$minors.']}';
        exit();
    }

?>
