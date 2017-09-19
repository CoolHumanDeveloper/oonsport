<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

//if (!isset($shoutbox_message_id) || $shoutbox_message_id == ""
//) {
//    header(HEADER_SERVERERR);
//    $response['code'] = MISSING_PARAMETER;
//    die(json_encode($response));
//}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$box_type = isset($box_type) ? $box_type : "inbox"; // inbox, send, trash, blocked

$sql = "SELECT * FROM message m, user_to_messages utm WHERE utm.user_id='".$user['user_id']."' AND m.message_id = utm.message_id AND utm.message_box = '".$box_type."' ORDER BY m.message_date DESC";

$query = $DB->prepare($sql);
$query->execute();
$get_messages = $query->fetchAll(PDO::FETCH_ASSOC);

$response['result'] = $get_messages;