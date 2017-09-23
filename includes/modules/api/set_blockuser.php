<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($block_user_id) || $block_user_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, ud.user_firstname FROM user u, user_details ud WHERE MD5(CONCAT(u.user_id,ud.user_nickname))='$block_user_id' AND u.user_id = ud.user_id LIMIT 1";

$query = $DB->prepare($sql);
$query->execute();
$blocked = $query->fetch();

if($user['user_id'] === $blocked['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = BLOCK_ME;
    die();
}

if (blockedlist_exists($user['user_id'],$blocked['user_id']) === true) {
    header(HEADER_SERVERERR);
    $response['code'] = ALREADY_BLOCKED;
    die();
}

$sql = "INSERT INTO `user_blocked` (`user_id`, `blocked_user_id`, `blocked_date`) VALUES ('" . $user['user_id'] . "', '" . $blocked['user_id'] . "', NOW());";
$query = $DB->prepare($sql);
$query->execute();

build_history_log($user['user_id'],"blocked", $blocked['user_id']);
