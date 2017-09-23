<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($group_id) || $group_id == ""
    || !isset($message) || $message == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "	SELECT 
			g.*,
			u.user_id 
		FROM 
			groups g
			LEFT JOIN user_to_groups utg ON g.group_id = utg.group_id
			LEFT JOIN user u ON u.user_id = g.group_admin_user_id
		WHERE
			g.group_id = '$group_id' AND
			u.user_status = 1			
		LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$group = $query->fetch();

if (is_group_member($group['group_id'], $user['user_id']) === false && is_group_invited($group['group_id'], $user['user_id']) === false) {
    header(HEADER_FORBIDDEN);
    $response['code'] = FAIL_USER;
    die(json_encode($response));
}

if(strlen($message) > 255) {
    header(HEADER_SERVERERR);
    $response['code'] = MESSAGE_LENGTH;
    die(json_encode($response));
}

$message = preg_replace("/'/", "\\'", $message);

$sql = "INSERT INTO `groups_message` (group_message_user_id, group_message_group_id, `group_message_date`, `group_message_value`) VALUES ('".$user['user_id']."', '$group_id',  NOW(), '$message');";
$query = $DB->prepare($sql);
$query->execute();
