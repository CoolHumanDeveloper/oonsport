<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($friend_id) || $friend_id == ""
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
$sql = "SELECT * FROM user_details WHERE user_id=" . $used_in_profile_id;
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);

$search_lat = $user_detail['user_lat'];
$search_lng = $user_detail['user_lng'];

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, ud.user_firstname FROM user u, user_details ud WHERE u.user_id='$friend_id' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$friendship_user = $query->fetch();

if ($used_in_profile_id === $friendship_user['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = PROCESS_ME;
    die();
}

$sql = "DELETE FROM `user_friendship` WHERE friendship_user_id='$friend_id' AND user_id = '$used_in_profile_id' OR user_id='$friend_id' AND friendship_user_id = '$used_in_profile_id'";
$query = $DB->prepare($sql);
$query->execute();

build_history_log($used_in_profile_id, "friendship_pre_deleted", $friend_id);

$response['sql'] = $sql;