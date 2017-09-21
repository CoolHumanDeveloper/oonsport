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

$sql = "SELECT * FROM 
	message m
	LEFT JOIN user_to_messages AS utm ON m.message_id = utm.message_id WHERE
	m.message_key = '$message_key' AND 
	(m.message_from_user_id = '" . $user['user_id'] . "' OR m.message_to_user_id = '" . $user['user_id'] . "') AND
	utm.user_id = '" . $user['user_id'] . "'";

$query = $DB->prepare($sql);
$query->execute();
$message = $query->fetch();

if (!isset($message['message_id'])) {
    header(HEADER_SERVERERR);
    $response['code'] = NOTFOUND_MESSAGE;
    die(json_encode($response));
}

if ($message['message_type'] == 'from') {
    $sql = "UPDATE user_to_messages SET message_box='send' WHERE user_id = '" . $user['user_id'] . "' AND message_id = '" . $message['message_id'] . "'";
    $query = $DB->prepare($sql);
    $query->execute();
} else {
    $sql = "UPDATE user_to_messages SET message_box='inbox' WHERE user_id = '" . $user['user_id'] . "' AND message_id = '" . $message['message_id'] . "'";
    $query = $DB->prepare($sql);
    $query->execute();
}