<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($blocked_user_id) || $blocked_user_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, ud.user_firstname FROM user u, user_details ud WHERE u.user_id='$blocked_user_id' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$blocked = $query->fetch();

if ($user['user_id'] === $blocked['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = BLOCK_ME;
    die(json_encode($response));
}

$sql = "DELETE FROM `user_blocked` WHERE user_id='" . $user['user_id'] . "' and blocked_user_id='$blocked_user_id'";
$query = $DB->prepare($sql);
$query->execute();

build_history_log($user['user_id'],"blocked_remove", $blocked['user_id']);
