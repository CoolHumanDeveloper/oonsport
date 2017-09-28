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

$used_in_profile_id = $user['user_id'];
if (isset($infos->used_in_profile))
    $used_in_profile_id = $infos->used_in_profile;

$box_type = isset($box_type) ? $box_type : "inbox"; // inbox, send, trash, blocked

$sql = "SELECT * FROM message m, user_to_messages utm WHERE utm.user_id='$used_in_profile_id' AND m.message_id = utm.message_id AND utm.message_box = '".$box_type."' ORDER BY m.message_date DESC";

$query = $DB->prepare($sql);
$query->execute();
$get_messages = $query->fetchAll(PDO::FETCH_ASSOC);


foreach($get_messages as $key => $item)
{
    $get_messages[$key]['sender_image'] = api_build_default_image( $item[ 'message_from_user_id' ], "50x50");
    $sql = "select * from user_details where user_id='{$item[ 'message_from_user_id' ]}'";
    $query = $DB->prepare($sql);
    $query->execute();
    $userinfo = $query->fetch(PDO::FETCH_ASSOC);
    $get_messages[$key]['sender_nickname'] = $userinfo['user_nickname'];
    $get_messages[$key]['sender_firstname'] = $userinfo['user_firstname'];
    $get_messages[$key]['sender_lastname'] = $userinfo['user_lastname'];

    $get_messages[$key]['receiver_image'] = api_build_default_image( $item[ 'message_to_user_id' ], "50x50");
    $sql = "select * from user_details where user_id='{$item[ 'message_to_user_id' ]}'";
    $query = $DB->prepare($sql);
    $query->execute();
    $userinfo = $query->fetch(PDO::FETCH_ASSOC);
    $get_messages[$key]['receiver_nickname'] = $userinfo['user_nickname'];
    $get_messages[$key]['receiver_firstname'] = $userinfo['user_firstname'];
    $get_messages[$key]['receiver_lastname'] = $userinfo['user_lastname'];
}

$response['result'] = $get_messages;