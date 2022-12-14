<?php
include_once "utils/sqldb.php";
if (isset($_POST["eventid"]) && $_POST["eventid"]!="") {
    $eventid=$_POST["eventid"];
}
else {
    echo '{"error":true,"msg":"There are no events selected"}';
    exit();
}

if (isset($_POST["winners"])) {
    $winners = json_decode(get("luckydraw","*","event_id=$eventid AND is_winner=1 ORDER BY selected_datetime"),true);
    $winner_list = [];
    foreach ($winners as $win) {
        $winner_list[]=$win["ticket"];
    }
    echo json_encode($winner_list);
}
else if (isset($_POST["select_winner"])) {
    $foundsomeone=false;
    $counter = 0;
    while (True) {
        $counter++;
        $count = (int)json_decode(get("registration_requests","COUNT(id) as num","event_id=$eventid", true),true)[0]["num"];
        if ($counter>$count) {
            break;
        }
        $randomwinner = json_decode(get("registration_requests","cid,other_cids","event_id=$eventid AND is_allowed=1 ORDER BY RAND() LIMIT 1"),true);
        if (empty($randomwinner)) {
            break;
        }
        $randomwinner_cids[] = $randomwinner[0]["cid"];
        foreach (explode(";",ltrim($randomwinner[0]["other_cids"],";")) as $c) {
            if ($c!="") {
                $randomwinner_cids[] = $c;
            }         
        }

        $randomwinner_cid = $randomwinner_cids[array_rand($randomwinner_cids)];

        if (get("luckydraw","cid","event_id=$eventid AND is_winner=1 AND cid='$randomwinner_cid'")=="[]") {
            $foundsomeone=true;
            break;
        }
    }

    if (!$foundsomeone) {
        echo '{"error":true,"msg":"There are not enough participants to select the winner"}';
        exit();
    }
    $eventdetail = json_decode(get("events","*","id=$eventid",true),true);
    if ($eventid=="4") {
        $ticket = strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$randomwinner_cid)*3,10,36));
    }
    else {
        $ticket = strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$randomwinner_cid),10,36));
    }
    
    echo insert("luckydraw","ticket,cid,event_id,is_winner","$ticket,$randomwinner_cid,$eventid,1");
    exit();
}