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
    while (True) {
        $randomwinner = json_decode(get("registration_requests","cid","event_id=$eventid AND is_allowed=1 ORDER BY RAND() LIMIT 1"),true);
        if (empty($randomwinner)) {
            break;
        }
        $randomwinner_cid = $randomwinner[0]["cid"];
        if (get("luckydraw","cid","event_id=$eventid AND is_winner=1 AND cid='$randomwinner_cid'")=="[]") {
            break;
        }
    }

    if (empty($randomwinner)) {
        echo '{"error":true,"msg":"There are not enough participants to select the winner"}';
        exit();
    }
    $eventdetail = json_decode(get("events","*","id=$eventid",true),true);
    $ticket = strtoupper(base_convert((string)((int)$eventdetail[0]["ticket_offset"]+(int)$randomwinner_cid),10,36));
    echo insert("luckdraw","ticket,cid,event_id,is_winner","$ticket,$cid,$eventid,1");
    exit();
}