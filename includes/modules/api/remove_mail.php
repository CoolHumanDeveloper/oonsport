<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($message_key) || $message_key == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$used_in_profile_id = $user['user_id'];
if (isset($infos->used_in_profile))
    $used_in_profile_id = $infos->used_in_profile;

$box_type = isset($box_type) ? $box_type : "inbox"; // inbox, send, trash, blocked

$sql = "SELECT * FROM 
	message m
	LEFT JOIN user_to_messages AS utm ON m.message_id = utm.message_id WHERE
	m.message_key = '$message_key' AND 
	(m.message_from_user_id = '$used_in_profile_id' OR m.message_to_user_id = '$used_in_profile_id') AND
	utm.user_id = '$used_in_profile_id' LIMIT 1";

$query = $DB->prepare($sql);
$query->execute();
$message = $query->fetch();

if (isset($message['message_id'])) {

    if($message['message_readed'] == 0 && $message['message_to_user_id'] === $used_in_profile_id){
        $sql = "UPDATE message SET message_readed=1 WHERE message_id = '".$message['message_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
    }

    $sql = "UPDATE user_to_messages SET message_box='trash', message_action_date = NOW() WHERE user_id = '$used_in_profile_id' AND message_id = '".$message['message_id']."'";
    $query = $DB->prepare($sql);
    $query->execute();
}
